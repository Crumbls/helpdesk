<?php

use Crumbls\HelpDesk\Models\Department;
use Crumbls\HelpDesk\Models\Priority;
use Crumbls\HelpDesk\Models\Ticket;
use Crumbls\HelpDesk\Models\TicketStatus;
use Crumbls\HelpDesk\Models\TicketType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

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

it('validates required fields on create', function () {
    $response = $this->postJson('/api/helpdesk/tickets', []);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors([
        'ticket_type_id',
        'ticket_status_id',
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
