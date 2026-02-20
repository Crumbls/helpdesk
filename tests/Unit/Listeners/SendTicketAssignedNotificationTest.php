<?php

use Crumbls\HelpDesk\Events\TicketAssigned;
use Crumbls\HelpDesk\Listeners\SendTicketAssignedNotification;
use Crumbls\HelpDesk\Models\Department;
use Crumbls\HelpDesk\Models\Priority;
use Crumbls\HelpDesk\Models\Ticket;
use Crumbls\HelpDesk\Models\TicketStatus;
use Crumbls\HelpDesk\Models\TicketType;
use Crumbls\HelpDesk\Notifications\TicketAssignedNotification;
use Crumbls\HelpDesk\Tests\Fixtures\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

function assignedTicket(): array
{
    $submitter = User::create(['name' => 'Sub', 'email' => 'sub@test.com']);
    $agent = User::create(['name' => 'Agent', 'email' => 'agent@test.com']);
    $status = TicketStatus::factory()->create();
    $type = TicketType::factory()->create();
    $dept = Department::factory()->create();
    $priority = Priority::factory()->create();

    $ticket = Ticket::factory()->create([
        'submitter_id' => $submitter->id,
        'ticket_status_id' => $status->id,
        'ticket_type_id' => $type->id,
        'department_id' => $dept->id,
        'priority_id' => $priority->id,
    ]);

    return [$ticket, $agent];
}

it('notifies the assigned agent', function () {
    Notification::fake();

    [$ticket, $agent] = assignedTicket();

    config(['helpdesk.notifications.agent_ticket_assigned' => true]);

    $listener = new SendTicketAssignedNotification();
    $listener->handle(new TicketAssigned($ticket, $agent, 'assignee'));

    Notification::assertSentTo($agent, TicketAssignedNotification::class);
});

it('skips notification when config toggle is off', function () {
    [$ticket, $agent] = assignedTicket();

    // Fake AFTER ticket creation so model-event notifications don't interfere
    Notification::fake();
    config(['helpdesk.notifications.agent_ticket_assigned' => false]);

    $listener = new SendTicketAssignedNotification();
    $listener->handle(new TicketAssigned($ticket, $agent, 'assignee'));

    Notification::assertNothingSent();
});
