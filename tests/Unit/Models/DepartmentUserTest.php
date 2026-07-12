<?php

use Crumbls\HelpDesk\Models\Department;
use Crumbls\HelpDesk\Tests\Fixtures\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can attach agents to departments with pivot metadata', function () {
    $department = Department::factory()->create();

    $agent = User::query()->create([
        'name' => 'Support Agent',
        'email' => 'agent@example.com',
    ]);

    $department->users()->attach($agent->id, [
        'role' => 'lead',
        'assigned_only' => true,
    ]);

    $department->load('users');

    expect($department->users)->toHaveCount(1)
        ->and($department->users->first()->pivot->role)->toBe('lead')
        ->and($department->users->first()->pivot->assigned_only)->toBeTrue();
});
