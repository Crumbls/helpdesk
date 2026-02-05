<?php

use Crumbls\HelpDesk\Models\TicketType;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can list types', function () {
    TicketType::factory()->count(3)->create();

    $response = $this->getJson('/api/helpdesk/types');

    $response->assertOk();
    $response->assertJsonCount(3, 'data');
});

it('returns paginated results on index', function () {
    TicketType::factory()->count(3)->create();

    $response = $this->getJson('/api/helpdesk/types');

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

it('can show a type', function () {
    $type = TicketType::factory()->create(['title' => 'Bug Report']);

    $response = $this->getJson("/api/helpdesk/types/{$type->id}");

    $response->assertOk();
    $response->assertJsonFragment(['title' => 'Bug Report']);
});

it('returns 404 for missing type on show', function () {
    $response = $this->getJson('/api/helpdesk/types/999');

    $response->assertNotFound();
    $response->assertJsonPath('error.message', 'Record not found');
});

it('can create a type', function () {
    $response = $this->postJson('/api/helpdesk/types', [
        'title' => 'Feature Request',
        'description' => 'Request for new features',
        'color_background' => '#3B82F6',
        'color_foreground' => '#ffffff',
        'is_active' => true,
    ]);

    $response->assertStatus(201);
    $response->assertJsonFragment(['title' => 'Feature Request']);

    $this->assertDatabaseHas('helpdesk_ticket_types', ['title' => 'Feature Request']);
});

it('validates required fields on create', function () {
    $response = $this->postJson('/api/helpdesk/types', []);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['title']);
});

it('can create a type with minimal fields', function () {
    $response = $this->postJson('/api/helpdesk/types', [
        'title' => 'Question',
    ]);

    $response->assertStatus(201);
    $response->assertJsonFragment(['title' => 'Question']);
});

it('can update a type', function () {
    $type = TicketType::factory()->create(['title' => 'Bug']);

    $response = $this->putJson("/api/helpdesk/types/{$type->id}", [
        'title' => 'Bug Report',
    ]);

    $response->assertOk();
    $response->assertJsonFragment(['title' => 'Bug Report']);

    $this->assertDatabaseHas('helpdesk_ticket_types', [
        'id' => $type->id,
        'title' => 'Bug Report',
    ]);
});

it('can update is_active on a type', function () {
    $type = TicketType::factory()->create(['is_active' => true]);

    $response = $this->putJson("/api/helpdesk/types/{$type->id}", [
        'is_active' => false,
    ]);

    $response->assertOk();
    $response->assertJsonFragment(['is_active' => false]);
});

it('returns 404 when updating missing type', function () {
    $response = $this->putJson('/api/helpdesk/types/999', [
        'title' => 'Nope',
    ]);

    $response->assertNotFound();
});

it('can delete a type', function () {
    $type = TicketType::factory()->create();

    $response = $this->deleteJson("/api/helpdesk/types/{$type->id}");

    $response->assertOk();
    $response->assertJsonFragment(['message' => 'Type deleted']);

    $this->assertSoftDeleted('helpdesk_ticket_types', ['id' => $type->id]);
});

it('returns 404 when deleting missing type', function () {
    $response = $this->deleteJson('/api/helpdesk/types/999');

    $response->assertNotFound();
});

it('returns json by default', function () {
    TicketType::factory()->create();

    $response = $this->getJson('/api/helpdesk/types');

    $response->assertHeader('content-type', 'application/json');
});

it('returns xml when requested', function () {
    $type = TicketType::factory()->create(['title' => 'Incident']);

    $response = $this->get("/api/helpdesk/types/{$type->id}", [
        'Accept' => 'application/xml',
    ]);

    $response->assertHeader('content-type', 'application/xml');
    expect($response->getContent())->toContain('Incident');
});
