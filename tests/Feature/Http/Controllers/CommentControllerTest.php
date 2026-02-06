<?php

use Crumbls\HelpDesk\Models\Department;
use Crumbls\HelpDesk\Models\Priority;
use Crumbls\HelpDesk\Models\Ticket;
use Crumbls\HelpDesk\Models\TicketComment;
use Crumbls\HelpDesk\Models\TicketStatus;
use Crumbls\HelpDesk\Models\TicketType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function createTestUser(array $attributes = []): int
{
    return DB::table('users')->insertGetId(array_merge([
        'name' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        'created_at' => now(),
        'updated_at' => now(),
    ], $attributes));
}

function createCommentDeps(): array
{
    $userId = createTestUser();
    $type = TicketType::factory()->create();
    $status = TicketStatus::factory()->create();
    $department = Department::factory()->create();
    $priority = Priority::factory()->create();

    $ticket = Ticket::factory()->create([
        'submitter_id' => $userId,
        'ticket_type_id' => $type->id,
        'ticket_status_id' => $status->id,
        'department_id' => $department->id,
        'priority_id' => $priority->id,
    ]);

    return compact('userId', 'ticket');
}

it('can list comments for a ticket', function () {
    $deps = createCommentDeps();

    TicketComment::factory()->count(3)->create([
        'ticket_id' => $deps['ticket']->id,
        'user_id' => $deps['userId'],
    ]);

    $response = $this->getJson("/api/helpdesk/comments?ticket_id={$deps['ticket']->id}");

    $response->assertOk();
    $response->assertJsonCount(3, 'data');
});

it('returns empty list for ticket without comments', function () {
    $deps = createCommentDeps();

    $response = $this->getJson("/api/helpdesk/comments?ticket_id={$deps['ticket']->id}");

    $response->assertOk();
    $response->assertJsonCount(0, 'data');
});

it('can show a single comment', function () {
    $deps = createCommentDeps();

    $comment = TicketComment::factory()->create([
        'ticket_id' => $deps['ticket']->id,
        'user_id' => $deps['userId'],
        'content' => 'Test comment content',
    ]);

    $response = $this->getJson("/api/helpdesk/comments/{$comment->id}");

    $response->assertOk();
    $response->assertJsonFragment(['content' => 'Test comment content']);
});

it('returns 404 for missing comment on show', function () {
    $response = $this->getJson('/api/helpdesk/comments/999');

    $response->assertNotFound();
    $response->assertJsonPath('error.message', 'Record not found');
});

it('can create a comment', function () {
    $deps = createCommentDeps();

    $response = $this->postJson('/api/helpdesk/comments', [
        'ticket_id' => $deps['ticket']->id,
        'user_id' => $deps['userId'],
        'content' => 'This is a new comment',
        'is_private' => false,
        'is_resolution' => false,
    ]);

    $response->assertStatus(201);
    $response->assertJsonFragment(['content' => 'This is a new comment']);

    $this->assertDatabaseHas('helpdesk_ticket_comments', ['content' => 'This is a new comment']);
});

it('validates required fields on create', function () {
    $response = $this->postJson('/api/helpdesk/comments', []);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['ticket_id', 'user_id', 'content']);
});

it('validates foreign key existence on create', function () {
    $userId = createTestUser();

    $response = $this->postJson('/api/helpdesk/comments', [
        'ticket_id' => 999,
        'user_id' => $userId,
        'content' => 'Test comment',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['ticket_id']);
});

it('can update a comment', function () {
    $deps = createCommentDeps();

    $comment = TicketComment::factory()->create([
        'ticket_id' => $deps['ticket']->id,
        'user_id' => $deps['userId'],
        'content' => 'Original content',
    ]);

    $response = $this->putJson("/api/helpdesk/comments/{$comment->id}", [
        'content' => 'Updated content',
    ]);

    $response->assertOk();
    $response->assertJsonFragment(['content' => 'Updated content']);

    $this->assertDatabaseHas('helpdesk_ticket_comments', [
        'id' => $comment->id,
        'content' => 'Updated content',
    ]);
});

it('returns 404 when updating missing comment', function () {
    $response = $this->putJson('/api/helpdesk/comments/999', [
        'content' => 'Nope',
    ]);

    $response->assertNotFound();
});

it('can delete a comment', function () {
    $deps = createCommentDeps();

    $comment = TicketComment::factory()->create([
        'ticket_id' => $deps['ticket']->id,
        'user_id' => $deps['userId'],
    ]);

    $response = $this->deleteJson("/api/helpdesk/comments/{$comment->id}");

    $response->assertOk();
    $response->assertJsonFragment(['message' => 'Comment deleted']);

    $this->assertSoftDeleted('helpdesk_ticket_comments', ['id' => $comment->id]);
});

it('returns 404 when deleting missing comment', function () {
    $response = $this->deleteJson('/api/helpdesk/comments/999');

    $response->assertNotFound();
});

it('unsets other resolution comments when creating with is_resolution true', function () {
    $deps = createCommentDeps();

    $existing = TicketComment::factory()->create([
        'ticket_id' => $deps['ticket']->id,
        'user_id' => $deps['userId'],
        'is_resolution' => true,
    ]);

    $response = $this->postJson('/api/helpdesk/comments', [
        'ticket_id' => $deps['ticket']->id,
        'user_id' => $deps['userId'],
        'content' => 'New resolution',
        'is_resolution' => true,
    ]);

    $response->assertStatus(201);

    expect($existing->fresh()->is_resolution)->toBeFalse();
    expect(TicketComment::where('ticket_id', $deps['ticket']->id)->where('is_resolution', true)->count())->toBe(1);
});

it('unsets other resolution comments when updating with is_resolution true', function () {
    $deps = createCommentDeps();

    $existing = TicketComment::factory()->create([
        'ticket_id' => $deps['ticket']->id,
        'user_id' => $deps['userId'],
        'is_resolution' => true,
    ]);

    $other = TicketComment::factory()->create([
        'ticket_id' => $deps['ticket']->id,
        'user_id' => $deps['userId'],
        'is_resolution' => false,
    ]);

    $response = $this->putJson("/api/helpdesk/comments/{$other->id}", [
        'is_resolution' => true,
    ]);

    $response->assertOk();

    expect($existing->fresh()->is_resolution)->toBeFalse();
    expect($other->fresh()->is_resolution)->toBeTrue();
});

it('filters by ticket_id', function () {
    $deps1 = createCommentDeps();
    $deps2 = createCommentDeps();

    TicketComment::factory()->count(2)->create([
        'ticket_id' => $deps1['ticket']->id,
        'user_id' => $deps1['userId'],
    ]);

    TicketComment::factory()->count(3)->create([
        'ticket_id' => $deps2['ticket']->id,
        'user_id' => $deps2['userId'],
    ]);

    $response = $this->getJson("/api/helpdesk/comments?ticket_id={$deps1['ticket']->id}");

    $response->assertOk();
    $response->assertJsonCount(2, 'data');
});

it('returns json by default', function () {
    $deps = createCommentDeps();

    TicketComment::factory()->create([
        'ticket_id' => $deps['ticket']->id,
        'user_id' => $deps['userId'],
    ]);

    $response = $this->getJson('/api/helpdesk/comments');

    $response->assertHeader('content-type', 'application/json');
});

it('returns xml when requested', function () {
    $deps = createCommentDeps();

    $comment = TicketComment::factory()->create([
        'ticket_id' => $deps['ticket']->id,
        'user_id' => $deps['userId'],
        'content' => 'XML Test Comment',
    ]);

    $response = $this->get("/api/helpdesk/comments/{$comment->id}", [
        'Accept' => 'application/xml',
    ]);

    $response->assertHeader('content-type', 'application/xml');
    expect($response->getContent())->toContain('XML Test Comment');
});

it('eager loads user on index', function () {
    $deps = createCommentDeps();

    TicketComment::factory()->create([
        'ticket_id' => $deps['ticket']->id,
        'user_id' => $deps['userId'],
    ]);

    $response = $this->getJson('/api/helpdesk/comments');

    $response->assertOk();
    $response->assertJsonStructure([
        'data' => [
            '*' => ['id', 'content', 'user'],
        ],
    ]);
});

it('eager loads user on show', function () {
    $deps = createCommentDeps();

    $comment = TicketComment::factory()->create([
        'ticket_id' => $deps['ticket']->id,
        'user_id' => $deps['userId'],
    ]);

    $response = $this->getJson("/api/helpdesk/comments/{$comment->id}");

    $response->assertOk();
    $response->assertJsonStructure(['id', 'content', 'user']);
});

it('can create a private comment', function () {
    $deps = createCommentDeps();

    $response = $this->postJson('/api/helpdesk/comments', [
        'ticket_id' => $deps['ticket']->id,
        'user_id' => $deps['userId'],
        'content' => 'Private note',
        'is_private' => true,
    ]);

    $response->assertStatus(201);
    $response->assertJsonFragment(['is_private' => true]);

    $this->assertDatabaseHas('helpdesk_ticket_comments', [
        'content' => 'Private note',
        'is_private' => true,
    ]);
});

it('returns paginated results on index', function () {
    $deps = createCommentDeps();

    TicketComment::factory()->count(3)->create([
        'ticket_id' => $deps['ticket']->id,
        'user_id' => $deps['userId'],
    ]);

    $response = $this->getJson('/api/helpdesk/comments');

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
