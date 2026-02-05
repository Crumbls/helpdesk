<?php

use Crumbls\HelpDesk\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can list departments', function () {
    Department::factory()->count(3)->create();

    $response = $this->getJson('/api/helpdesk/departments');

    $response->assertOk();
    $response->assertJsonCount(3, 'data');
});

it('returns paginated results on index', function () {
    Department::factory()->count(3)->create();

    $response = $this->getJson('/api/helpdesk/departments');

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

it('can show a department', function () {
    $department = Department::factory()->create(['title' => 'Engineering']);

    $response = $this->getJson("/api/helpdesk/departments/{$department->id}");

    $response->assertOk();
    $response->assertJsonFragment(['title' => 'Engineering']);
});

it('returns 404 for missing department on show', function () {
    $response = $this->getJson('/api/helpdesk/departments/999');

    $response->assertNotFound();
    $response->assertJsonPath('error.message', 'Record not found');
});

it('can create a department', function () {
    $response = $this->postJson('/api/helpdesk/departments', [
        'title' => 'Customer Support',
        'description' => 'Handles customer inquiries',
        'color_background' => '#3B82F6',
        'color_foreground' => '#ffffff',
        'is_active' => true,
    ]);

    $response->assertStatus(201);
    $response->assertJsonFragment(['title' => 'Customer Support']);

    $this->assertDatabaseHas('helpdesk_departments', ['title' => 'Customer Support']);
});

it('validates required fields on create', function () {
    $response = $this->postJson('/api/helpdesk/departments', []);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['title']);
});

it('can create a department with minimal fields', function () {
    $response = $this->postJson('/api/helpdesk/departments', [
        'title' => 'Sales',
    ]);

    $response->assertStatus(201);
    $response->assertJsonFragment(['title' => 'Sales']);
});

it('can update a department', function () {
    $department = Department::factory()->create(['title' => 'Support']);

    $response = $this->putJson("/api/helpdesk/departments/{$department->id}", [
        'title' => 'Customer Support',
    ]);

    $response->assertOk();
    $response->assertJsonFragment(['title' => 'Customer Support']);

    $this->assertDatabaseHas('helpdesk_departments', [
        'id' => $department->id,
        'title' => 'Customer Support',
    ]);
});

it('can update is_active on a department', function () {
    $department = Department::factory()->create(['is_active' => true]);

    $response = $this->putJson("/api/helpdesk/departments/{$department->id}", [
        'is_active' => false,
    ]);

    $response->assertOk();
    $response->assertJsonFragment(['is_active' => false]);
});

it('returns 404 when updating missing department', function () {
    $response = $this->putJson('/api/helpdesk/departments/999', [
        'title' => 'Nope',
    ]);

    $response->assertNotFound();
});

it('can delete a department', function () {
    $department = Department::factory()->create();

    $response = $this->deleteJson("/api/helpdesk/departments/{$department->id}");

    $response->assertOk();
    $response->assertJsonFragment(['message' => 'Department deleted']);

    $this->assertSoftDeleted('helpdesk_departments', ['id' => $department->id]);
});

it('returns 404 when deleting missing department', function () {
    $response = $this->deleteJson('/api/helpdesk/departments/999');

    $response->assertNotFound();
});

it('returns json by default', function () {
    Department::factory()->create();

    $response = $this->getJson('/api/helpdesk/departments');

    $response->assertHeader('content-type', 'application/json');
});

it('returns xml when requested', function () {
    $department = Department::factory()->create(['title' => 'Billing']);

    $response = $this->get("/api/helpdesk/departments/{$department->id}", [
        'Accept' => 'application/xml',
    ]);

    $response->assertHeader('content-type', 'application/xml');
    expect($response->getContent())->toContain('Billing');
});
