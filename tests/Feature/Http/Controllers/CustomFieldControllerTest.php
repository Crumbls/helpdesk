<?php

use Crumbls\HelpDesk\Models\CustomField;
use Crumbls\HelpDesk\Models\Department;
use Crumbls\HelpDesk\Models\TicketType;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createCustomFieldDeps(): array
{
    return [
        'department' => Department::factory()->create(),
        'type' => TicketType::factory()->create(),
    ];
}

it('can list custom fields', function () {
    CustomField::query()->create([
        'key' => 'customer_plan',
        'label' => 'Customer Plan',
        'type' => CustomField::TYPE_TEXT,
    ]);

    $response = $this->getJson('/api/helpdesk/custom-fields');

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
    $response->assertJsonFragment(['key' => 'customer_plan']);
});

it('can create a custom field', function () {
    $deps = createCustomFieldDeps();

    $response = $this->postJson('/api/helpdesk/custom-fields', [
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
        'sort_order' => 10,
    ]);

    $response->assertCreated();
    $response->assertJsonFragment([
        'key' => 'customer_plan',
        'label' => 'Customer Plan',
        'is_required' => true,
    ]);

    $this->assertDatabaseHas('helpdesk_custom_fields', [
        'key' => 'customer_plan',
        'department_id' => $deps['department']->id,
        'ticket_type_id' => $deps['type']->id,
    ]);
});

it('requires options for choice custom fields', function () {
    $response = $this->postJson('/api/helpdesk/custom-fields', [
        'key' => 'customer_plan',
        'label' => 'Customer Plan',
        'type' => CustomField::TYPE_SELECT,
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['options']);
});

it('validates custom field keys', function () {
    $response = $this->postJson('/api/helpdesk/custom-fields', [
        'key' => 'Customer Plan',
        'label' => 'Customer Plan',
        'type' => CustomField::TYPE_TEXT,
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['key']);
});

it('can update a custom field', function () {
    $field = CustomField::query()->create([
        'key' => 'customer_plan',
        'label' => 'Customer Plan',
        'type' => CustomField::TYPE_TEXT,
        'is_required' => false,
    ]);

    $response = $this->putJson("/api/helpdesk/custom-fields/{$field->id}", [
        'label' => 'Plan',
        'is_required' => true,
    ]);

    $response->assertOk();
    $response->assertJsonFragment([
        'label' => 'Plan',
        'is_required' => true,
    ]);
});

it('can delete a custom field', function () {
    $field = CustomField::query()->create([
        'key' => 'customer_plan',
        'label' => 'Customer Plan',
        'type' => CustomField::TYPE_TEXT,
    ]);

    $response = $this->deleteJson("/api/helpdesk/custom-fields/{$field->id}");

    $response->assertOk();
    $response->assertJsonFragment(['message' => 'Custom field deleted']);

    $this->assertSoftDeleted('helpdesk_custom_fields', ['id' => $field->id]);
});
