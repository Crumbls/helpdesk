<?php

use Crumbls\HelpDesk\Events\TicketCreated;
use Crumbls\HelpDesk\Listeners\SendTicketCreatedNotifications;
use Crumbls\HelpDesk\Models\Ticket;
use Crumbls\HelpDesk\Notifications\AdminNewTicket;
use Crumbls\HelpDesk\Notifications\TicketConfirmation;
use Crumbls\HelpDesk\Tests\Fixtures\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

it('notifies admins and submitter on ticket created', function () {
    Notification::fake();

    $admin = User::create(['name' => 'Admin', 'email' => 'admin@test.com']);
    $submitter = User::create(['name' => 'Sub', 'email' => 'sub@test.com']);

    config(['helpdesk.notifications.admin_user_ids' => [$admin->id]]);
    config(['helpdesk.notifications.admin_new_ticket' => true]);
    config(['helpdesk.notifications.submitter_confirmation' => true]);

    $ticket = Ticket::factory()->create(['submitter_id' => $submitter->id]);

    $listener = new SendTicketCreatedNotifications();
    $listener->handle(new TicketCreated($ticket));

    Notification::assertSentTo($admin, AdminNewTicket::class);
    Notification::assertSentTo($submitter, TicketConfirmation::class);
});

it('skips admin notification when disabled', function () {
    Notification::fake();

    $admin = User::create(['name' => 'Admin', 'email' => 'admin2@test.com']);
    $submitter = User::create(['name' => 'Sub', 'email' => 'sub2@test.com']);

    config(['helpdesk.notifications.admin_user_ids' => [$admin->id]]);
    config(['helpdesk.notifications.admin_new_ticket' => false]);
    config(['helpdesk.notifications.submitter_confirmation' => true]);

    $ticket = Ticket::factory()->create(['submitter_id' => $submitter->id]);

    $listener = new SendTicketCreatedNotifications();
    $listener->handle(new TicketCreated($ticket));

    Notification::assertNotSentTo($admin, AdminNewTicket::class);
    Notification::assertSentTo($submitter, TicketConfirmation::class);
});

it('skips submitter confirmation when disabled', function () {
    $submitter = User::create(['name' => 'Sub', 'email' => 'sub3@test.com']);

    $ticket = Ticket::factory()->create(['submitter_id' => $submitter->id]);

    // Fake AFTER factory creation to avoid capturing factory-triggered notifications
    Notification::fake();
    config(['helpdesk.notifications.admin_new_ticket' => false]);
    config(['helpdesk.notifications.submitter_confirmation' => false]);

    $listener = new SendTicketCreatedNotifications();
    $listener->handle(new TicketCreated($ticket));

    Notification::assertNothingSent();
});

it('resolves admins by email config', function () {
    Notification::fake();

    $admin = User::create(['name' => 'EmailAdmin', 'email' => 'emailadmin@test.com']);
    $submitter = User::create(['name' => 'Sub', 'email' => 'sub4@test.com']);

    config(['helpdesk.notifications.admin_user_ids' => []]);
    config(['helpdesk.notifications.admin_emails' => ['emailadmin@test.com']]);
    config(['helpdesk.notifications.admin_new_ticket' => true]);
    config(['helpdesk.notifications.submitter_confirmation' => false]);

    $ticket = Ticket::factory()->create(['submitter_id' => $submitter->id]);

    $listener = new SendTicketCreatedNotifications();
    $listener->handle(new TicketCreated($ticket));

    Notification::assertSentTo($admin, AdminNewTicket::class);
});
