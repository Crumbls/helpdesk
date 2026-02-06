<?php

use Crumbls\HelpDesk\Events\CommentCreated;
use Crumbls\HelpDesk\Events\CommentDeleted;
use Crumbls\HelpDesk\Events\CommentUpdated;
use Crumbls\HelpDesk\Models\Ticket;
use Crumbls\HelpDesk\Models\TicketComment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

function createCommentEventTestUser(array $attributes = []): int
{
    return DB::table('users')->insertGetId(array_merge([
        'name' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        'created_at' => now(),
        'updated_at' => now(),
    ], $attributes));
}

beforeEach(function () {
    config(['helpdesk.events.enabled' => true]);
    config(['helpdesk.events.dispatch.comment_created' => true]);
    config(['helpdesk.events.dispatch.comment_updated' => true]);
    config(['helpdesk.events.dispatch.comment_deleted' => true]);
});

it('dispatches CommentCreated event when comment is created', function () {
    Event::fake([CommentCreated::class]);

    $userId = createCommentEventTestUser();
    $ticket = Ticket::factory()->create(['submitter_id' => $userId]);

    $comment = TicketComment::create([
        'ticket_id' => $ticket->id,
        'user_id' => $userId,
        'content' => 'Test comment',
        'is_private' => false,
    ]);

    Event::assertDispatched(CommentCreated::class, function ($event) use ($comment) {
        return $event->comment->id === $comment->id;
    });
});

it('dispatches CommentUpdated event when comment is updated', function () {
    Event::fake([CommentUpdated::class]);

    $userId = createCommentEventTestUser();
    $ticket = Ticket::factory()->create(['submitter_id' => $userId]);
    $comment = TicketComment::factory()->create([
        'ticket_id' => $ticket->id,
        'user_id' => $userId,
    ]);

    $comment->update(['content' => 'Updated content']);

    Event::assertDispatched(CommentUpdated::class, function ($event) use ($comment) {
        return $event->comment->id === $comment->id
            && isset($event->changes['content']);
    });
});

it('dispatches CommentDeleted event when comment is deleted', function () {
    Event::fake([CommentDeleted::class]);

    $userId = createCommentEventTestUser();
    $ticket = Ticket::factory()->create(['submitter_id' => $userId]);
    $comment = TicketComment::factory()->create([
        'ticket_id' => $ticket->id,
        'user_id' => $userId,
    ]);
    $commentId = $comment->id;

    $comment->delete();

    Event::assertDispatched(CommentDeleted::class, function ($event) use ($commentId) {
        return $event->comment->id === $commentId;
    });
});

it('does not dispatch events when disabled in config', function () {
    config(['helpdesk.events.enabled' => false]);

    Event::fake([CommentCreated::class]);

    $userId = createCommentEventTestUser();
    $ticket = Ticket::factory()->create(['submitter_id' => $userId]);
    TicketComment::factory()->create([
        'ticket_id' => $ticket->id,
        'user_id' => $userId,
    ]);

    Event::assertNotDispatched(CommentCreated::class);
});

it('does not dispatch specific event when that event is disabled', function () {
    config(['helpdesk.events.dispatch.comment_created' => false]);

    Event::fake([CommentCreated::class]);

    $userId = createCommentEventTestUser();
    $ticket = Ticket::factory()->create(['submitter_id' => $userId]);
    TicketComment::factory()->create([
        'ticket_id' => $ticket->id,
        'user_id' => $userId,
    ]);

    Event::assertNotDispatched(CommentCreated::class);
});
