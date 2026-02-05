<?php

use Crumbls\HelpDesk\Models\TicketStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can list statuses', function () {
    TicketStatus::factory()->count(3)->create();

    $response = $this->getJson('/api/helpdesk/statuses');

    $response->assertOk();
    $response->assertJsonCount(3, 'data');
});

it('returns paginated results on index', function () {
    TicketStatus::factory()->count(3)->create();

    $response = $this->getJson('/api/helpdesk/statuses');

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

it('can show a status', function () {
    $status = TicketStatus::factory()->create(['title' => 'In Progress']);

    $response = $this->getJson("/api/helpdesk/statuses/{$status->id}");

    $response->assertOk();
    $response->assertJsonFragment(['title' => 'In Progress']);
});

it('returns 404 for missing status on show', function () {
    $response = $this->getJson('/api/helpdesk/statuses/999');

    $response->assertNotFound();
    $response->assertJsonPath('error.message', 'Record not found');
});

it('can create a status', function () {
    $response = $this->postJson('/api/helpdesk/statuses', [
        'title' => 'Resolved',
        'description' => 'Issue has been resolved',
        'color_background' => '#10B981',
        'color_foreground' => '#ffffff',
        'is_active' => true,
        'is_default' => false,
        'is_closed' => true,
    ]);

    $response->assertStatus(201);
    $response->assertJsonFragment(['title' => 'Resolved']);
    $response->assertJsonFragment(['is_closed' => true]);

    $this->assertDatabaseHas('helpdesk_ticket_statuses', ['title' => 'Resolved']);
});

it('validates required fields on create', function () {
    $response = $this->postJson('/api/helpdesk/statuses', []);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['title']);
});

it('can create a status with is_closed false', function () {
    $response = $this->postJson('/api/helpdesk/statuses', [
        'title' => 'Open',
        'is_closed' => false,
    ]);

    $response->assertStatus(201);
    $response->assertJsonFragment(['is_closed' => false]);
});

it('can update a status', function () {
    $status = TicketStatus::factory()->create(['title' => 'New']);

    $response = $this->putJson("/api/helpdesk/statuses/{$status->id}", [
        'title' => 'In Review',
    ]);

    $response->assertOk();
    $response->assertJsonFragment(['title' => 'In Review']);

    $this->assertDatabaseHas('helpdesk_ticket_statuses', [
        'id' => $status->id,
        'title' => 'In Review',
    ]);
});

it('can update is_closed on a status', function () {
    $status = TicketStatus::factory()->create(['is_closed' => false]);

    $response = $this->putJson("/api/helpdesk/statuses/{$status->id}", [
        'is_closed' => true,
    ]);

    $response->assertOk();
    $response->assertJsonFragment(['is_closed' => true]);
});

it('returns 404 when updating missing status', function () {
    $response = $this->putJson('/api/helpdesk/statuses/999', [
        'title' => 'Nope',
    ]);

    $response->assertNotFound();
});

it('can delete a status', function () {
    $status = TicketStatus::factory()->create();

    $response = $this->deleteJson("/api/helpdesk/statuses/{$status->id}");

    $response->assertOk();
    $response->assertJsonFragment(['message' => 'Status deleted']);

    $this->assertSoftDeleted('helpdesk_ticket_statuses', ['id' => $status->id]);
});

it('returns 404 when deleting missing status', function () {
    $response = $this->deleteJson('/api/helpdesk/statuses/999');

    $response->assertNotFound();
});

it('unsets other defaults when creating with is_default true', function () {
    $existing = TicketStatus::factory()->create(['is_default' => true]);

    $response = $this->postJson('/api/helpdesk/statuses', [
        'title' => 'New Default',
        'is_default' => true,
    ]);

    $response->assertStatus(201);

    expect($existing->fresh()->is_default)->toBeFalse();
    expect(TicketStatus::where('is_default', true)->count())->toBe(1);
});

it('unsets other defaults when updating with is_default true', function () {
    $existing = TicketStatus::factory()->create(['is_default' => true]);
    $other = TicketStatus::factory()->create(['is_default' => false]);

    $response = $this->putJson("/api/helpdesk/statuses/{$other->id}", [
        'is_default' => true,
    ]);

    $response->assertOk();

    expect($existing->fresh()->is_default)->toBeFalse();
    expect($other->fresh()->is_default)->toBeTrue();
});

it('returns json by default', function () {
    TicketStatus::factory()->create();

    $response = $this->getJson('/api/helpdesk/statuses');

    $response->assertHeader('content-type', 'application/json');
});

it('returns xml when requested', function () {
    $status = TicketStatus::factory()->create(['title' => 'Open']);

    $response = $this->get("/api/helpdesk/statuses/{$status->id}", [
        'Accept' => 'application/xml',
    ]);

    $response->assertHeader('content-type', 'application/xml');
    expect($response->getContent())->toContain('Open');
});
