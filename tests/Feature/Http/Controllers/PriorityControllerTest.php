<?php

use Crumbls\HelpDesk\Models\Priority;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can list priorities', function () {
    Priority::factory()->count(3)->create();

    $response = $this->getJson('/api/helpdesk/priorities');

    $response->assertOk();
    $response->assertJsonCount(3, 'data');
});

it('returns paginated results on index', function () {
    Priority::factory()->count(3)->create();

    $response = $this->getJson('/api/helpdesk/priorities');

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

it('can show a priority', function () {
    $priority = Priority::factory()->create(['title' => 'Urgent']);

    $response = $this->getJson("/api/helpdesk/priorities/{$priority->id}");

    $response->assertOk();
    $response->assertJsonFragment(['title' => 'Urgent']);
});

it('returns 404 for missing priority on show', function () {
    $response = $this->getJson('/api/helpdesk/priorities/999');

    $response->assertNotFound();
    $response->assertJsonPath('error.message', 'Record not found');
});

it('can create a priority', function () {
    $response = $this->postJson('/api/helpdesk/priorities', [
        'title' => 'Critical',
        'description' => 'Highest priority',
        'color_background' => '#EF4444',
        'color_foreground' => '#ffffff',
        'level' => 5,
        'is_active' => true,
        'is_default' => false,
    ]);

    $response->assertStatus(201);
    $response->assertJsonFragment(['title' => 'Critical']);

    $this->assertDatabaseHas('helpdesk_priorities', ['title' => 'Critical']);
});

it('validates required fields on create', function () {
    $response = $this->postJson('/api/helpdesk/priorities', []);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['title']);
});

it('can update a priority', function () {
    $priority = Priority::factory()->create(['title' => 'Low']);

    $response = $this->putJson("/api/helpdesk/priorities/{$priority->id}", [
        'title' => 'Medium',
    ]);

    $response->assertOk();
    $response->assertJsonFragment(['title' => 'Medium']);

    $this->assertDatabaseHas('helpdesk_priorities', [
        'id' => $priority->id,
        'title' => 'Medium',
    ]);
});

it('returns 404 when updating missing priority', function () {
    $response = $this->putJson('/api/helpdesk/priorities/999', [
        'title' => 'Nope',
    ]);

    $response->assertNotFound();
});

it('can delete a priority', function () {
    $priority = Priority::factory()->create();

    $response = $this->deleteJson("/api/helpdesk/priorities/{$priority->id}");

    $response->assertOk();
    $response->assertJsonFragment(['message' => 'Priority deleted']);

    $this->assertSoftDeleted('helpdesk_priorities', ['id' => $priority->id]);
});

it('returns 404 when deleting missing priority', function () {
    $response = $this->deleteJson('/api/helpdesk/priorities/999');

    $response->assertNotFound();
});

it('unsets other defaults when creating with is_default true', function () {
    $existing = Priority::factory()->create(['is_default' => true]);

    $response = $this->postJson('/api/helpdesk/priorities', [
        'title' => 'New Default',
        'is_default' => true,
    ]);

    $response->assertStatus(201);

    expect($existing->fresh()->is_default)->toBeFalse();
    expect(Priority::where('is_default', true)->count())->toBe(1);
});

it('unsets other defaults when updating with is_default true', function () {
    $existing = Priority::factory()->create(['is_default' => true]);
    $other = Priority::factory()->create(['is_default' => false]);

    $response = $this->putJson("/api/helpdesk/priorities/{$other->id}", [
        'is_default' => true,
    ]);

    $response->assertOk();

    expect($existing->fresh()->is_default)->toBeFalse();
    expect($other->fresh()->is_default)->toBeTrue();
});

it('returns json by default', function () {
    Priority::factory()->create();

    $response = $this->getJson('/api/helpdesk/priorities');

    $response->assertHeader('content-type', 'application/json');
});

it('returns xml when requested', function () {
    Priority::factory()->create(['title' => 'Urgent']);

    $response = $this->get('/api/helpdesk/priorities/1', [
        'Accept' => 'application/xml',
    ]);

    $response->assertHeader('content-type', 'application/xml');
    expect($response->getContent())->toContain('Urgent');
});
