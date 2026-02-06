<?php

use Carbon\Carbon;
use Crumbls\HelpDesk\Models\Priority;
use Crumbls\HelpDesk\Services\SlaService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->slaService = new SlaService();
    config(['helpdesk.sla.business_hours.enabled' => false]);
});

it('calculates response due date using priority sla hours', function () {
    $priority = Priority::factory()->create([
        'sla_response_hours' => 4,
    ]);

    $createdAt = Carbon::parse('2025-01-15 10:00:00');
    $dueAt = $this->slaService->calculateResponseDue($priority->id, $createdAt);

    expect($dueAt->format('Y-m-d H:i:s'))->toBe('2025-01-15 14:00:00');
});

it('calculates resolution due date using priority sla hours', function () {
    $priority = Priority::factory()->create([
        'sla_resolution_hours' => 48,
    ]);

    $createdAt = Carbon::parse('2025-01-15 10:00:00');
    $dueAt = $this->slaService->calculateResolutionDue($priority->id, $createdAt);

    expect($dueAt->format('Y-m-d H:i:s'))->toBe('2025-01-17 10:00:00');
});

it('falls back to config defaults when priority has no sla hours', function () {
    config(['helpdesk.sla.defaults.response_hours' => 24]);
    config(['helpdesk.sla.defaults.resolution_hours' => 72]);

    $priority = Priority::factory()->create([
        'sla_response_hours' => null,
        'sla_resolution_hours' => null,
    ]);

    $createdAt = Carbon::parse('2025-01-15 10:00:00');

    $responseDue = $this->slaService->calculateResponseDue($priority->id, $createdAt);
    $resolutionDue = $this->slaService->calculateResolutionDue($priority->id, $createdAt);

    expect($responseDue->format('Y-m-d H:i:s'))->toBe('2025-01-16 10:00:00');
    expect($resolutionDue->format('Y-m-d H:i:s'))->toBe('2025-01-18 10:00:00');
});

it('detects response breach when no response and past due', function () {
    $responseDue = Carbon::parse('2025-01-15 10:00:00');

    Carbon::setTestNow(Carbon::parse('2025-01-15 12:00:00'));

    expect($this->slaService->isResponseBreached($responseDue, null))->toBeTrue();

    Carbon::setTestNow();
});

it('detects response breach when response was after due date', function () {
    $responseDue = Carbon::parse('2025-01-15 10:00:00');
    $firstResponseAt = Carbon::parse('2025-01-15 12:00:00');

    expect($this->slaService->isResponseBreached($responseDue, $firstResponseAt))->toBeTrue();
});

it('does not flag breach when response was before due date', function () {
    $responseDue = Carbon::parse('2025-01-15 10:00:00');
    $firstResponseAt = Carbon::parse('2025-01-15 08:00:00');

    expect($this->slaService->isResponseBreached($responseDue, $firstResponseAt))->toBeFalse();
});

it('detects resolution breach when not closed and past due', function () {
    $resolutionDue = Carbon::parse('2025-01-15 10:00:00');

    Carbon::setTestNow(Carbon::parse('2025-01-16 10:00:00'));

    expect($this->slaService->isResolutionBreached($resolutionDue, null))->toBeTrue();

    Carbon::setTestNow();
});

it('detects resolution breach when closed after due date', function () {
    $resolutionDue = Carbon::parse('2025-01-15 10:00:00');
    $closedAt = Carbon::parse('2025-01-16 10:00:00');

    expect($this->slaService->isResolutionBreached($resolutionDue, $closedAt))->toBeTrue();
});

it('does not flag breach when closed before due date', function () {
    $resolutionDue = Carbon::parse('2025-01-15 10:00:00');
    $closedAt = Carbon::parse('2025-01-14 10:00:00');

    expect($this->slaService->isResolutionBreached($resolutionDue, $closedAt))->toBeFalse();
});

// Business hours tests
// Business hours: 9:00-17:00 (8 hours per day), Mon-Fri
// 2025-01-13: Monday, 2025-01-14: Tuesday, 2025-01-15: Wednesday
// 2025-01-18: Saturday, 2025-01-19: Sunday

it('calculates SLA within same business day', function () {
    config(['helpdesk.sla.business_hours.enabled' => true]);
    config(['helpdesk.sla.business_hours.timezone' => 'UTC']);
    config(['helpdesk.sla.business_hours.start' => '09:00']);
    config(['helpdesk.sla.business_hours.end' => '17:00']);
    config(['helpdesk.sla.business_hours.days' => [1, 2, 3, 4, 5]]);

    $priority = Priority::factory()->create([
        'sla_response_hours' => 4,
    ]);

    // Wednesday 10:00 + 4 hours = Wednesday 14:00
    $createdAt = Carbon::parse('2025-01-15 10:00:00', 'UTC');
    $dueAt = $this->slaService->calculateResponseDue($priority->id, $createdAt);

    expect($dueAt->format('Y-m-d H:i:s'))->toBe('2025-01-15 14:00:00');
});

it('calculates SLA spanning multiple business days', function () {
    config(['helpdesk.sla.business_hours.enabled' => true]);
    config(['helpdesk.sla.business_hours.timezone' => 'UTC']);
    config(['helpdesk.sla.business_hours.start' => '09:00']);
    config(['helpdesk.sla.business_hours.end' => '17:00']);
    config(['helpdesk.sla.business_hours.days' => [1, 2, 3, 4, 5]]);

    $priority = Priority::factory()->create([
        'sla_response_hours' => 12,
    ]);

    // Wednesday 10:00 + 12 business hours
    // Wed 10:00-17:00 = 7 hours, remaining 5 hours
    // Thu 09:00-14:00 = 5 hours
    $createdAt = Carbon::parse('2025-01-15 10:00:00', 'UTC');
    $dueAt = $this->slaService->calculateResponseDue($priority->id, $createdAt);

    expect($dueAt->format('Y-m-d H:i:s'))->toBe('2025-01-16 14:00:00');
});

it('skips weekends when calculating business hours SLA', function () {
    config(['helpdesk.sla.business_hours.enabled' => true]);
    config(['helpdesk.sla.business_hours.timezone' => 'UTC']);
    config(['helpdesk.sla.business_hours.start' => '09:00']);
    config(['helpdesk.sla.business_hours.end' => '17:00']);
    config(['helpdesk.sla.business_hours.days' => [1, 2, 3, 4, 5]]);

    $priority = Priority::factory()->create([
        'sla_response_hours' => 4,
    ]);

    // Friday 15:00 + 4 business hours
    // Fri 15:00-17:00 = 2 hours, remaining 2 hours
    // Skip Sat/Sun
    // Mon 09:00-11:00 = 2 hours
    $createdAt = Carbon::parse('2025-01-17 15:00:00', 'UTC');
    $dueAt = $this->slaService->calculateResponseDue($priority->id, $createdAt);

    expect($dueAt->format('Y-m-d H:i:s'))->toBe('2025-01-20 11:00:00');
});

it('starts from next business day when created on weekend', function () {
    config(['helpdesk.sla.business_hours.enabled' => true]);
    config(['helpdesk.sla.business_hours.timezone' => 'UTC']);
    config(['helpdesk.sla.business_hours.start' => '09:00']);
    config(['helpdesk.sla.business_hours.end' => '17:00']);
    config(['helpdesk.sla.business_hours.days' => [1, 2, 3, 4, 5]]);

    $priority = Priority::factory()->create([
        'sla_response_hours' => 4,
    ]);

    // Saturday 12:00 + 4 business hours
    // Skip to Monday 09:00, then add 4 hours = 13:00
    $createdAt = Carbon::parse('2025-01-18 12:00:00', 'UTC');
    $dueAt = $this->slaService->calculateResponseDue($priority->id, $createdAt);

    expect($dueAt->format('Y-m-d H:i:s'))->toBe('2025-01-20 13:00:00');
});

it('starts from business hours start when created before opening', function () {
    config(['helpdesk.sla.business_hours.enabled' => true]);
    config(['helpdesk.sla.business_hours.timezone' => 'UTC']);
    config(['helpdesk.sla.business_hours.start' => '09:00']);
    config(['helpdesk.sla.business_hours.end' => '17:00']);
    config(['helpdesk.sla.business_hours.days' => [1, 2, 3, 4, 5]]);

    $priority = Priority::factory()->create([
        'sla_response_hours' => 4,
    ]);

    // Wednesday 06:00 (before business hours) + 4 hours
    // Starts at 09:00, add 4 hours = 13:00
    $createdAt = Carbon::parse('2025-01-15 06:00:00', 'UTC');
    $dueAt = $this->slaService->calculateResponseDue($priority->id, $createdAt);

    expect($dueAt->format('Y-m-d H:i:s'))->toBe('2025-01-15 13:00:00');
});

it('starts from next business day when created after closing', function () {
    config(['helpdesk.sla.business_hours.enabled' => true]);
    config(['helpdesk.sla.business_hours.timezone' => 'UTC']);
    config(['helpdesk.sla.business_hours.start' => '09:00']);
    config(['helpdesk.sla.business_hours.end' => '17:00']);
    config(['helpdesk.sla.business_hours.days' => [1, 2, 3, 4, 5]]);

    $priority = Priority::factory()->create([
        'sla_response_hours' => 4,
    ]);

    // Wednesday 19:00 (after business hours) + 4 hours
    // Skip to Thursday 09:00, add 4 hours = 13:00
    $createdAt = Carbon::parse('2025-01-15 19:00:00', 'UTC');
    $dueAt = $this->slaService->calculateResponseDue($priority->id, $createdAt);

    expect($dueAt->format('Y-m-d H:i:s'))->toBe('2025-01-16 13:00:00');
});
