<?php

use Crumbls\HelpDesk\Events\CommentCreated;
use Crumbls\HelpDesk\Listeners\SendCommentCreatedNotifications;
use Crumbls\HelpDesk\Models\Ticket;
use Crumbls\HelpDesk\Models\TicketComment;
use Crumbls\HelpDesk\Notifications\AdminNewComment;
use Crumbls\HelpDesk\Notifications\CommentReply;
use Crumbls\HelpDesk\Tests\Fixtures\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

it('notifies admins when submitter comments publicly', function () {
    Notification::fake();

    $admin = User::create(['name' => 'Admin', 'email' => 'admin@test.com']);
    $submitter = User::create(['name' => 'Sub', 'email' => 'sub@test.com']);

    config(['helpdesk.notifications.admin_user_ids' => [$admin->id]]);
    config(['helpdesk.notifications.admin_new_comment' => true]);

    $ticket = Ticket::factory()->create(['submitter_id' => $submitter->id]);
    $comment = TicketComment::factory()->create([
        'ticket_id' => $ticket->id,
        'user_id' => $submitter->id,
        'is_private' => false,
    ]);

    $listener = new SendCommentCreatedNotifications();
    $listener->handle(new CommentCreated($comment));

    Notification::assertSentTo($admin, AdminNewComment::class);
    Notification::assertNotSentTo($submitter, CommentReply::class);
});

it('notifies submitter when agent comments publicly', function () {
    Notification::fake();

    $admin = User::create(['name' => 'Admin', 'email' => 'admin2@test.com']);
    $agent = User::create(['name' => 'Agent', 'email' => 'agent@test.com']);
    $submitter = User::create(['name' => 'Sub', 'email' => 'sub2@test.com']);

    config(['helpdesk.notifications.admin_user_ids' => [$admin->id]]);
    config(['helpdesk.notifications.admin_new_comment' => true]);
    config(['helpdesk.notifications.submitter_comment_reply' => true]);

    $ticket = Ticket::factory()->create(['submitter_id' => $submitter->id]);
    $comment = TicketComment::factory()->create([
        'ticket_id' => $ticket->id,
        'user_id' => $agent->id,
        'is_private' => false,
    ]);

    $listener = new SendCommentCreatedNotifications();
    $listener->handle(new CommentCreated($comment));

    Notification::assertSentTo($submitter, CommentReply::class);
});

it('only notifies admins on private comments', function () {
    Notification::fake();

    $admin = User::create(['name' => 'Admin', 'email' => 'admin3@test.com']);
    $submitter = User::create(['name' => 'Sub', 'email' => 'sub3@test.com']);
    $agent = User::create(['name' => 'Agent', 'email' => 'agent2@test.com']);

    config(['helpdesk.notifications.admin_user_ids' => [$admin->id]]);
    config(['helpdesk.notifications.admin_new_comment' => true]);

    $ticket = Ticket::factory()->create(['submitter_id' => $submitter->id]);
    $comment = TicketComment::factory()->create([
        'ticket_id' => $ticket->id,
        'user_id' => $agent->id,
        'is_private' => true,
    ]);

    $listener = new SendCommentCreatedNotifications();
    $listener->handle(new CommentCreated($comment));

    Notification::assertSentTo($admin, AdminNewComment::class);
    Notification::assertNotSentTo($submitter, CommentReply::class);
});

it('excludes commenter from admin notifications', function () {
    Notification::fake();

    // Admin IS the commenter
    $admin = User::create(['name' => 'AdminAgent', 'email' => 'adminagent@test.com']);
    $submitter = User::create(['name' => 'Sub', 'email' => 'sub4@test.com']);

    config(['helpdesk.notifications.admin_user_ids' => [$admin->id]]);
    config(['helpdesk.notifications.admin_new_comment' => true]);
    config(['helpdesk.notifications.submitter_comment_reply' => true]);

    $ticket = Ticket::factory()->create(['submitter_id' => $submitter->id]);
    $comment = TicketComment::factory()->create([
        'ticket_id' => $ticket->id,
        'user_id' => $admin->id,
        'is_private' => false,
    ]);

    $listener = new SendCommentCreatedNotifications();
    $listener->handle(new CommentCreated($comment));

    // Admin should be excluded since they're the commenter
    Notification::assertNotSentTo($admin, AdminNewComment::class);
    // Submitter should get the reply
    Notification::assertSentTo($submitter, CommentReply::class);
});
