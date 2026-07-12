<?php

use Crumbls\HelpDesk\Events\TicketAssigned;
use Crumbls\HelpDesk\Models\CustomField;
use Crumbls\HelpDesk\Models\Department;
use Crumbls\HelpDesk\Models\Priority;
use Crumbls\HelpDesk\Models\Ticket;
use Crumbls\HelpDesk\Models\TicketAssignment;
use Crumbls\HelpDesk\Models\TicketComment;
use Crumbls\HelpDesk\Models\TicketStatus;
use Crumbls\HelpDesk\Models\TicketType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

function createUser(array $attributes = []): int
{
    return DB::table('users')->insertGetId(array_merge([
        'name' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        'created_at' => now(),
        'updated_at' => now(),
    ], $attributes));
}

function createTicketDeps(): array
{
    $userId = createUser();
    $type = TicketType::factory()->create();
    $status = TicketStatus::factory()->create();
    $department = Department::factory()->create();
    $priority = Priority::factory()->create();

    return compact('userId', 'type', 'status', 'department', 'priority');
}

it('can list tickets', function () {
    $deps = createTicketDeps();

    Ticket::factory()->count(3)->create(['submitter_id' => $deps['userId']]);

    $response = $this->getJson('/api/helpdesk/tickets');

    $response->assertOk();
    $response->assertJsonCount(3, 'data');
});

it('returns paginated results on index', function () {
    $deps = createTicketDeps();

    Ticket::factory()->count(3)->create(['submitter_id' => $deps['userId']]);

    $response = $this->getJson('/api/helpdesk/tickets');

    $response->assertOk();
    $response->assertJsonStructure([
        'current_page',
        'data',
        'first_page_url',
        'last_page',
        'per_page',
        'total',
    ]);
});

it('orders tickets by last activity', function () {
    $deps = createTicketDeps();

    $older = Ticket::factory()->create([
        'submitter_id' => $deps['userId'],
        'last_activity_at' => Carbon::parse('2026-01-01 10:00:00'),
    ]);

    $newer = Ticket::factory()->create([
        'submitter_id' => $deps['userId'],
        'last_activity_at' => Carbon::parse('2026-01-01 11:00:00'),
    ]);

    $response = $this->getJson('/api/helpdesk/tickets');

    $response->assertOk();
    expect($response->json('data.0.id'))->toBe($newer->id);
    expect($response->json('data.1.id'))->toBe($older->id);
});

it('can filter open and closed ticket queues', function () {
    $deps = createTicketDeps();
    $closedStatus = TicketStatus::factory()->closed()->create();

    $openTicket = Ticket::factory()->create([
        'submitter_id' => $deps['userId'],
        'ticket_status_id' => $deps['status']->id,
    ]);

    $closedTicket = Ticket::factory()->create([
        'submitter_id' => $deps['userId'],
        'ticket_status_id' => $closedStatus->id,
    ]);

    $this->getJson('/api/helpdesk/tickets?queue=open')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $openTicket->id);

    $this->getJson('/api/helpdesk/tickets?queue=closed')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $closedTicket->id);
});

it('can filter department and assignment ticket queues', function () {
    $deps = createTicketDeps();
    $agentId = createUser();
    $otherDepartment = Department::factory()->create();

    $assignedTicket = Ticket::factory()->create([
        'submitter_id' => $deps['userId'],
        'department_id' => $deps['department']->id,
    ]);

    $unassignedTicket = Ticket::factory()->create([
        'submitter_id' => $deps['userId'],
        'department_id' => $otherDepartment->id,
    ]);

    $assignedTicket->assignedUsers()->attach($agentId, ['role' => 'assignee']);

    $this->getJson("/api/helpdesk/tickets?department_id={$deps['department']->id}")
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $assignedTicket->id);

    $this->getJson("/api/helpdesk/tickets?assigned_to={$agentId}")
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $assignedTicket->id);

    $this->getJson("/api/helpdesk/tickets?queue=assigned_to_me&user_id={$agentId}")
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $assignedTicket->id);

    $this->getJson('/api/helpdesk/tickets?queue=unassigned')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $unassignedTicket->id);
});

it('can show a ticket', function () {
    $deps = createTicketDeps();

    $ticket = Ticket::factory()->create([
        'submitter_id' => $deps['userId'],
        'title' => 'Login broken',
    ]);

    $response = $this->getJson("/api/helpdesk/tickets/{$ticket->id}");

    $response->assertOk();
    $response->assertJsonFragment(['title' => 'Login broken']);
});

it('returns 404 for missing ticket on show', function () {
    $response = $this->getJson('/api/helpdesk/tickets/999');

    $response->assertNotFound();
    $response->assertJsonPath('error.message', 'Record not found');
});

it('can create a ticket', function () {
    $deps = createTicketDeps();

    $response = $this->postJson('/api/helpdesk/tickets', [
        'ticket_type_id' => $deps['type']->id,
        'ticket_status_id' => $deps['status']->id,
        'submitter_id' => $deps['userId'],
        'department_id' => $deps['department']->id,
        'priority_id' => $deps['priority']->id,
        'title' => 'Cannot log in',
        'description' => 'Getting 500 error on login page',
        'source' => 'email',
    ]);

    $response->assertStatus(201);
    $response->assertJsonFragment(['title' => 'Cannot log in']);
    $response->assertJsonFragment(['source' => 'email']);

    $this->assertDatabaseHas('helpdesk_tickets', ['title' => 'Cannot log in']);
});

it('sets last activity when creating a ticket', function () {
    try {
        Carbon::setTestNow(Carbon::parse('2026-01-01 10:00:00'));

        $deps = createTicketDeps();

        $response = $this->postJson('/api/helpdesk/tickets', [
            'ticket_type_id' => $deps['type']->id,
            'ticket_status_id' => $deps['status']->id,
            'submitter_id' => $deps['userId'],
            'title' => 'Activity check',
            'description' => 'The ticket should be activity-sortable immediately.',
        ]);

        $response->assertCreated();

        $ticket = Ticket::query()->findOrFail($response->json('id'));

        expect($ticket->last_activity_at?->toDateTimeString())->toBe('2026-01-01 10:00:00');
    } finally {
        Carbon::setTestNow();
    }
});

it('generates a ticket reference from the persisted id', function () {
    $deps = createTicketDeps();

    $response = $this->postJson('/api/helpdesk/tickets', [
        'ticket_type_id' => $deps['type']->id,
        'ticket_status_id' => $deps['status']->id,
        'submitter_id' => $deps['userId'],
        'title' => 'Reference check',
        'description' => 'The reference should be based on the inserted row id.',
    ]);

    $response->assertCreated();

    $ticket = Ticket::query()->findOrFail($response->json('id'));

    expect($ticket->reference)->toBe(Ticket::referenceForId($ticket->id));
});

it('validates required fields on create', function () {
    $response = $this->postJson('/api/helpdesk/tickets', []);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors([
        'submitter_id',
        'title',
        'description',
    ]);
});

it('validates foreign key existence on create', function () {
    $userId = createUser();

    $response = $this->postJson('/api/helpdesk/tickets', [
        'ticket_type_id' => 999,
        'ticket_status_id' => 999,
        'submitter_id' => $userId,
        'title' => 'Test',
        'description' => 'Test description',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['ticket_type_id', 'ticket_status_id']);
});

it('can create a ticket with optional fields', function () {
    $deps = createTicketDeps();

    $response = $this->postJson('/api/helpdesk/tickets', [
        'ticket_type_id' => $deps['type']->id,
        'ticket_status_id' => $deps['status']->id,
        'submitter_id' => $deps['userId'],
        'title' => 'Feature request',
        'description' => 'Please add dark mode',
        'resolution' => null,
        'due_at' => '2026-03-01',
    ]);

    $response->assertStatus(201);
    $response->assertJsonFragment(['title' => 'Feature request']);
});

it('can create a ticket with custom fields', function () {
    $deps = createTicketDeps();

    CustomField::query()->create([
        'key' => 'customer_plan',
        'label' => 'Customer Plan',
        'type' => CustomField::TYPE_SELECT,
        'options' => [
            'free' => 'Free',
            'pro' => 'Pro',
        ],
        'department_id' => $deps['department']->id,
        'ticket_type_id' => $deps['type']->id,
        'is_required' => true,
    ]);

    $response = $this->postJson('/api/helpdesk/tickets', [
        'ticket_type_id' => $deps['type']->id,
        'ticket_status_id' => $deps['status']->id,
        'submitter_id' => $deps['userId'],
        'department_id' => $deps['department']->id,
        'title' => 'Billing question',
        'description' => 'Need help with my plan',
        'custom_fields' => [
            'customer_plan' => 'pro',
        ],
    ]);

    $response->assertCreated();

    $ticket = Ticket::query()->findOrFail($response->json('id'));

    expect($ticket->custom_fields)->toBe(['customer_plan' => 'pro']);
});

it('requires configured custom fields when creating tickets', function () {
    $deps = createTicketDeps();

    CustomField::query()->create([
        'key' => 'customer_plan',
        'label' => 'Customer Plan',
        'type' => CustomField::TYPE_TEXT,
        'department_id' => $deps['department']->id,
        'ticket_type_id' => $deps['type']->id,
        'is_required' => true,
    ]);

    $response = $this->postJson('/api/helpdesk/tickets', [
        'ticket_type_id' => $deps['type']->id,
        'ticket_status_id' => $deps['status']->id,
        'submitter_id' => $deps['userId'],
        'department_id' => $deps['department']->id,
        'title' => 'Billing question',
        'description' => 'Need help with my plan',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['custom_fields.customer_plan']);
});

it('rejects invalid custom field option values when creating tickets', function () {
    $deps = createTicketDeps();

    CustomField::query()->create([
        'key' => 'customer_plan',
        'label' => 'Customer Plan',
        'type' => CustomField::TYPE_RADIO,
        'options' => [
            'free' => 'Free',
            'pro' => 'Pro',
        ],
        'is_required' => true,
    ]);

    $response = $this->postJson('/api/helpdesk/tickets', [
        'ticket_type_id' => $deps['type']->id,
        'ticket_status_id' => $deps['status']->id,
        'submitter_id' => $deps['userId'],
        'title' => 'Billing question',
        'description' => 'Need help with my plan',
        'custom_fields' => [
            'customer_plan' => 'enterprise',
        ],
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['custom_fields.customer_plan']);
});

it('merges custom fields when updating tickets', function () {
    $deps = createTicketDeps();

    CustomField::query()->create([
        'key' => 'customer_plan',
        'label' => 'Customer Plan',
        'type' => CustomField::TYPE_TEXT,
        'is_required' => true,
    ]);

    CustomField::query()->create([
        'key' => 'account_id',
        'label' => 'Account ID',
        'type' => CustomField::TYPE_TEXT,
    ]);

    $ticket = Ticket::factory()->create([
        'submitter_id' => $deps['userId'],
        'custom_fields' => [
            'customer_plan' => 'pro',
        ],
    ]);

    $response = $this->putJson("/api/helpdesk/tickets/{$ticket->id}", [
        'custom_fields' => [
            'account_id' => 'acct_123',
        ],
    ]);

    $response->assertOk();

    expect($ticket->fresh()->custom_fields)->toBe([
        'customer_plan' => 'pro',
        'account_id' => 'acct_123',
    ]);
});

it('updates last activity when a comment is created', function () {
    try {
        $deps = createTicketDeps();
        $ticket = Ticket::factory()->create([
            'submitter_id' => $deps['userId'],
            'last_activity_at' => Carbon::parse('2026-01-01 10:00:00'),
        ]);

        Carbon::setTestNow(Carbon::parse('2026-01-01 11:00:00'));

        TicketComment::factory()->create([
            'ticket_id' => $ticket->id,
            'user_id' => $deps['userId'],
            'created_at' => Carbon::parse('2026-01-01 11:00:00'),
            'updated_at' => Carbon::parse('2026-01-01 11:00:00'),
        ]);

        expect($ticket->fresh()->last_activity_at?->toDateTimeString())->toBe('2026-01-01 11:00:00');
    } finally {
        Carbon::setTestNow();
    }
});

it('can mark tickets read and filter unread tickets', function () {
    try {
        $deps = createTicketDeps();
        $readerId = createUser();

        Carbon::setTestNow(Carbon::parse('2026-01-01 10:00:00'));

        $ticket = Ticket::factory()->create([
            'submitter_id' => $deps['userId'],
        ]);

        $this->getJson("/api/helpdesk/tickets?unread_for={$readerId}")
            ->assertOk()
            ->assertJsonCount(1, 'data');

        Carbon::setTestNow(Carbon::parse('2026-01-01 10:05:00'));

        $this->postJson("/api/helpdesk/tickets/{$ticket->id}/read", [
            'user_id' => $readerId,
        ])
            ->assertOk()
            ->assertJsonFragment(['message' => 'Ticket marked read']);

        expect($ticket->fresh()->isUnreadFor($readerId))->toBeFalse();

        $this->getJson("/api/helpdesk/tickets?unread_for={$readerId}")
            ->assertOk()
            ->assertJsonCount(0, 'data');

        Carbon::setTestNow(Carbon::parse('2026-01-01 10:10:00'));

        TicketComment::factory()->create([
            'ticket_id' => $ticket->id,
            'user_id' => $deps['userId'],
            'created_at' => Carbon::parse('2026-01-01 10:10:00'),
            'updated_at' => Carbon::parse('2026-01-01 10:10:00'),
        ]);

        expect($ticket->fresh()->isUnreadFor($readerId))->toBeTrue();

        $this->getJson("/api/helpdesk/tickets?unread_for={$readerId}")
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->postJson("/api/helpdesk/tickets/{$ticket->id}/read", [
            'user_id' => $readerId,
        ])->assertOk();

        $this->postJson("/api/helpdesk/tickets/{$ticket->id}/unread", [
            'user_id' => $readerId,
        ])
            ->assertOk()
            ->assertJsonFragment(['message' => 'Ticket marked unread']);

        expect($ticket->fresh()->isUnreadFor($readerId))->toBeTrue();
    } finally {
        Carbon::setTestNow();
    }
});

it('can assign and unassign a ticket', function () {
    Event::fake([TicketAssigned::class]);

    $deps = createTicketDeps();
    $agentId = createUser();
    $ticket = Ticket::factory()->create(['submitter_id' => $deps['userId']]);

    $this->postJson("/api/helpdesk/tickets/{$ticket->id}/assign", [
        'user_id' => $agentId,
    ])
        ->assertOk()
        ->assertJsonFragment(['message' => 'Ticket assigned']);

    $this->assertDatabaseHas('helpdesk_ticket_assignments', [
        'ticket_id' => $ticket->id,
        'user_id' => $agentId,
        'role' => 'assignee',
    ]);

    Event::assertDispatched(TicketAssigned::class);

    $this->postJson("/api/helpdesk/tickets/{$ticket->id}/unassign", [
        'user_id' => $agentId,
    ])
        ->assertOk()
        ->assertJsonFragment(['message' => 'Ticket unassigned']);

    $this->assertDatabaseMissing('helpdesk_ticket_assignments', [
        'ticket_id' => $ticket->id,
        'user_id' => $agentId,
        'role' => 'assignee',
    ]);
});

it('can assign the least loaded department agent', function () {
    $deps = createTicketDeps();
    $busyAgentId = createUser();
    $availableAgentId = createUser();
    $ticket = Ticket::factory()->create([
        'submitter_id' => $deps['userId'],
        'department_id' => $deps['department']->id,
    ]);
    $existingTicket = Ticket::factory()->create(['submitter_id' => $deps['userId']]);

    $deps['department']->users()->attach($busyAgentId, ['role' => 'agent']);
    $deps['department']->users()->attach($availableAgentId, ['role' => 'agent']);

    TicketAssignment::query()->create([
        'ticket_id' => $existingTicket->id,
        'user_id' => $busyAgentId,
        'role' => 'assignee',
    ]);

    $this->postJson("/api/helpdesk/tickets/{$ticket->id}/assign", [
        'strategy' => 'least_loaded',
    ])->assertOk();

    $this->assertDatabaseHas('helpdesk_ticket_assignments', [
        'ticket_id' => $ticket->id,
        'user_id' => $availableAgentId,
        'role' => 'assignee',
    ]);
});

it('can round robin department agents', function () {
    $deps = createTicketDeps();
    $firstAgentId = createUser();
    $secondAgentId = createUser();
    $firstTicket = Ticket::factory()->create([
        'submitter_id' => $deps['userId'],
        'department_id' => $deps['department']->id,
    ]);
    $secondTicket = Ticket::factory()->create([
        'submitter_id' => $deps['userId'],
        'department_id' => $deps['department']->id,
    ]);

    $deps['department']->users()->attach($firstAgentId, ['role' => 'agent']);
    $deps['department']->users()->attach($secondAgentId, ['role' => 'agent']);

    Cache::forget('helpdesk:assignment:round-robin:'.sha1("department:{$deps['department']->id}"));

    $this->postJson("/api/helpdesk/tickets/{$firstTicket->id}/assign", [
        'strategy' => 'round_robin',
    ])->assertOk();

    $this->postJson("/api/helpdesk/tickets/{$secondTicket->id}/assign", [
        'strategy' => 'round_robin',
    ])->assertOk();

    $this->assertDatabaseHas('helpdesk_ticket_assignments', [
        'ticket_id' => $firstTicket->id,
        'user_id' => min($firstAgentId, $secondAgentId),
        'role' => 'assignee',
    ]);

    $this->assertDatabaseHas('helpdesk_ticket_assignments', [
        'ticket_id' => $secondTicket->id,
        'user_id' => max($firstAgentId, $secondAgentId),
        'role' => 'assignee',
    ]);
});

it('can create a ticket with parent ticket', function () {
    $deps = createTicketDeps();

    $parent = Ticket::factory()->create(['submitter_id' => $deps['userId']]);

    $response = $this->postJson('/api/helpdesk/tickets', [
        'ticket_type_id' => $deps['type']->id,
        'ticket_status_id' => $deps['status']->id,
        'submitter_id' => $deps['userId'],
        'title' => 'Sub-task',
        'description' => 'Child of parent ticket',
        'parent_ticket_id' => $parent->id,
    ]);

    $response->assertStatus(201);
    $response->assertJsonFragment(['parent_ticket_id' => $parent->id]);
});

it('can update a ticket', function () {
    $deps = createTicketDeps();

    $ticket = Ticket::factory()->create(['submitter_id' => $deps['userId']]);

    $response = $this->putJson("/api/helpdesk/tickets/{$ticket->id}", [
        'title' => 'Updated title',
    ]);

    $response->assertOk();
    $response->assertJsonFragment(['title' => 'Updated title']);

    $this->assertDatabaseHas('helpdesk_tickets', [
        'id' => $ticket->id,
        'title' => 'Updated title',
    ]);
});

it('can update ticket status', function () {
    $deps = createTicketDeps();

    $ticket = Ticket::factory()->create(['submitter_id' => $deps['userId']]);
    $closedStatus = TicketStatus::factory()->closed()->create();

    $response = $this->putJson("/api/helpdesk/tickets/{$ticket->id}", [
        'ticket_status_id' => $closedStatus->id,
        'closed_at' => '2026-02-05',
        'resolution' => 'Fixed the bug',
    ]);

    $response->assertOk();
    $response->assertJsonFragment(['ticket_status_id' => $closedStatus->id]);
    $response->assertJsonFragment(['resolution' => 'Fixed the bug']);
});

it('returns 404 when updating missing ticket', function () {
    $response = $this->putJson('/api/helpdesk/tickets/999', [
        'title' => 'Nope',
    ]);

    $response->assertNotFound();
});

it('can delete a ticket', function () {
    $deps = createTicketDeps();

    $ticket = Ticket::factory()->create(['submitter_id' => $deps['userId']]);

    $response = $this->deleteJson("/api/helpdesk/tickets/{$ticket->id}");

    $response->assertOk();
    $response->assertJsonFragment(['message' => 'Ticket deleted']);

    $this->assertSoftDeleted('helpdesk_tickets', ['id' => $ticket->id]);
});

it('returns 404 when deleting missing ticket', function () {
    $response = $this->deleteJson('/api/helpdesk/tickets/999');

    $response->assertNotFound();
});

it('uses default status when ticket_status_id is omitted', function () {
    $deps = createTicketDeps();
    $defaultStatus = TicketStatus::factory()->create(['is_default' => true, 'title' => 'Open']);

    $response = $this->postJson('/api/helpdesk/tickets', [
        'ticket_type_id' => $deps['type']->id,
        'submitter_id' => $deps['userId'],
        'title' => 'No status provided',
        'description' => 'Should get default status',
    ]);

    $response->assertStatus(201);
    $response->assertJsonFragment(['ticket_status_id' => $defaultStatus->id]);
});

it('returns error when no status provided and no default exists', function () {
    $deps = createTicketDeps();

    TicketStatus::query()->update(['is_default' => false]);

    $response = $this->postJson('/api/helpdesk/tickets', [
        'ticket_type_id' => $deps['type']->id,
        'submitter_id' => $deps['userId'],
        'title' => 'No status',
        'description' => 'No default configured',
    ]);

    $response->assertStatus(422);
    $response->assertJsonPath('error.message', 'No default status configured');
});

it('returns json by default', function () {
    $deps = createTicketDeps();

    Ticket::factory()->create(['submitter_id' => $deps['userId']]);

    $response = $this->getJson('/api/helpdesk/tickets');

    $response->assertHeader('content-type', 'application/json');
});

it('returns xml when requested', function () {
    $deps = createTicketDeps();

    $ticket = Ticket::factory()->create([
        'submitter_id' => $deps['userId'],
        'title' => 'XML Test Ticket',
    ]);

    $response = $this->get("/api/helpdesk/tickets/{$ticket->id}", [
        'Accept' => 'application/xml',
    ]);

    $response->assertHeader('content-type', 'application/xml');
    expect($response->getContent())->toContain('XML Test Ticket');
});
