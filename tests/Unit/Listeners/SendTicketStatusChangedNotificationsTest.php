<?php

use Crumbls\HelpDesk\Events\TicketStatusChanged;
use Crumbls\HelpDesk\Listeners\SendTicketStatusChangedNotifications;
use Crumbls\HelpDesk\Models\Department;
use Crumbls\HelpDesk\Models\Priority;
use Crumbls\HelpDesk\Models\Ticket;
use Crumbls\HelpDesk\Models\TicketStatus;
use Crumbls\HelpDesk\Models\TicketType;
use Crumbls\HelpDesk\Notifications\TicketStatusUpdated;
use Crumbls\HelpDesk\Tests\Fixtures\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

function statusChangeTicket(): array
{
    $submitter = User::create(['name' => 'Sub', 'email' => 'sub@test.com']);
    $oldStatus = TicketStatus::factory()->create();
    $newStatus = TicketStatus::factory()->closed()->create();
    $type = TicketType::factory()->create();
    $dept = Department::factory()->create();
    $priority = Priority::factory()->create();

    $ticket = Ticket::factory()->create([
        'submitter_id' => $submitter->id,
        'ticket_status_id' => $oldStatus->id,
        'ticket_type_id' => $type->id,
        'department_id' => $dept->id,
        'priority_id' => $priority->id,
    ]);

    return [$ticket, $submitter, $oldStatus, $newStatus];
}

it('notifies submitter when ticket status changes', function () {
    Notification::fake();

    [$ticket, $submitter, $oldStatus, $newStatus] = statusChangeTicket();

    config(['helpdesk.notifications.submitter_status_changed' => true]);

    $listener = new SendTicketStatusChangedNotifications();
    $listener->handle(new TicketStatusChanged($ticket, $oldStatus->id, $newStatus->id));

    Notification::assertSentTo($submitter, TicketStatusUpdated::class);
});

it('does not notify when config toggle is off', function () {
    [$ticket, $submitter, $oldStatus, $newStatus] = statusChangeTicket();

    // Fake AFTER ticket creation so model-event notifications don't interfere
    Notification::fake();
    config(['helpdesk.notifications.submitter_status_changed' => false]);

    $listener = new SendTicketStatusChangedNotifications();
    $listener->handle(new TicketStatusChanged($ticket, $oldStatus->id, $newStatus->id));

    Notification::assertNothingSent();
});
