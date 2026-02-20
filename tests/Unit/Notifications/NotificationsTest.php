<?php

use Crumbls\HelpDesk\Models\Ticket;
use Crumbls\HelpDesk\Models\TicketComment;
use Crumbls\HelpDesk\Models\SatisfactionRating;
use Crumbls\HelpDesk\Notifications\AdminNewTicket;
use Crumbls\HelpDesk\Notifications\AdminNewComment;
use Crumbls\HelpDesk\Notifications\CommentReply;
use Crumbls\HelpDesk\Notifications\GuestLoginLink;
use Crumbls\HelpDesk\Notifications\GuestTicketLookup;
use Crumbls\HelpDesk\Notifications\TicketAssignedNotification;
use Crumbls\HelpDesk\Notifications\TicketConfirmation;
use Crumbls\HelpDesk\Notifications\TicketStatusUpdated;
use Crumbls\HelpDesk\Tests\Fixtures\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('AdminNewTicket sends via mail and has correct toArray', function () {
    $submitter = User::create(['name' => 'Sub', 'email' => 'sub@test.com']);
    $ticket = Ticket::factory()->create(['submitter_id' => $submitter->id, 'title' => 'Test']);

    $notification = new AdminNewTicket($ticket);
    $notifiable = User::create(['name' => 'Admin', 'email' => 'admin@test.com']);

    expect($notification->via($notifiable))->toBe(['mail']);

    $array = $notification->toArray($notifiable);
    expect($array['ticket_id'])->toBe($ticket->id);
    expect($array['type'])->toBe('new_ticket');
});

it('TicketConfirmation sends via mail', function () {
    $submitter = User::create(['name' => 'Sub', 'email' => 'sub2@test.com']);
    $ticket = Ticket::factory()->create(['submitter_id' => $submitter->id]);

    $notification = new TicketConfirmation($ticket);

    expect($notification->via($submitter))->toBe(['mail']);

    $array = $notification->toArray($submitter);
    expect($array['type'])->toBe('ticket_confirmation');
});

it('AdminNewComment sends via mail', function () {
    $submitter = User::create(['name' => 'Sub', 'email' => 'sub3@test.com']);
    $ticket = Ticket::factory()->create(['submitter_id' => $submitter->id]);
    $comment = TicketComment::factory()->create(['ticket_id' => $ticket->id, 'user_id' => $submitter->id]);

    $notification = new AdminNewComment($comment);
    $admin = User::create(['name' => 'Admin', 'email' => 'admin2@test.com']);

    expect($notification->via($admin))->toBe(['mail']);
});

it('GuestLoginLink sends via mail', function () {
    $submitter = User::create(['name' => 'Sub', 'email' => 'sub4@test.com']);
    $ticket = Ticket::factory()->create(['submitter_id' => $submitter->id]);

    $notification = new GuestLoginLink('https://example.com/login', $ticket);

    expect($notification->via($submitter))->toBe(['mail']);
});

it('GuestTicketLookup sends via mail', function () {
    $submitter = User::create(['name' => 'Sub', 'email' => 'sub5@test.com']);

    $notification = new GuestTicketLookup('https://example.com/tickets', 3);

    expect($notification->via($submitter))->toBe(['mail']);
});

it('TicketAssignedNotification sends via mail', function () {
    $submitter = User::create(['name' => 'Sub', 'email' => 'sub6@test.com']);
    $agent = User::create(['name' => 'Agent', 'email' => 'agent@test.com']);
    $ticket = Ticket::factory()->create(['submitter_id' => $submitter->id]);

    $notification = new TicketAssignedNotification($ticket, 'assignee');

    expect($notification->via($agent))->toBe(['mail']);
});

it('TicketStatusUpdated sends via mail', function () {
    $submitter = User::create(['name' => 'Sub', 'email' => 'sub7@test.com']);
    $ticket = Ticket::factory()->create(['submitter_id' => $submitter->id]);

    $notification = new TicketStatusUpdated($ticket, 1, 2);

    expect($notification->via($submitter))->toBe(['mail']);
});
