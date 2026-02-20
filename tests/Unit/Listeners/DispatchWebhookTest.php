<?php

use Crumbls\HelpDesk\Events\TicketCreated;
use Crumbls\HelpDesk\Events\CommentCreated;
use Crumbls\HelpDesk\Listeners\DispatchWebhook;
use Crumbls\HelpDesk\Models\Ticket;
use Crumbls\HelpDesk\Models\TicketComment;
use Crumbls\HelpDesk\Tests\Fixtures\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('sends webhook on ticket created', function () {
    Http::fake();

    $submitter = User::create(['name' => 'Sub', 'email' => 'sub@test.com']);

    config(['helpdesk.webhooks' => [
        ['url' => 'https://example.com/hook', 'events' => ['ticket_created'], 'secret' => ''],
    ]]);

    $ticket = Ticket::factory()->create(['submitter_id' => $submitter->id]);

    $listener = new DispatchWebhook();
    $listener->handle(new TicketCreated($ticket));

    Http::assertSent(function ($request) {
        return $request->url() === 'https://example.com/hook'
            && $request['event'] === 'ticket_created';
    });
});

it('includes hmac signature when secret configured', function () {
    Http::fake();

    $submitter = User::create(['name' => 'Sub', 'email' => 'sub2@test.com']);

    config(['helpdesk.webhooks' => [
        ['url' => 'https://example.com/hook', 'events' => ['*'], 'secret' => 'my-secret'],
    ]]);

    $ticket = Ticket::factory()->create(['submitter_id' => $submitter->id]);

    $listener = new DispatchWebhook();
    $listener->handle(new TicketCreated($ticket));

    Http::assertSent(function ($request) {
        return $request->hasHeader('X-Helpdesk-Signature');
    });
});

it('filters events based on webhook config', function () {
    Http::fake();

    $submitter = User::create(['name' => 'Sub', 'email' => 'sub3@test.com']);

    config(['helpdesk.webhooks' => [
        ['url' => 'https://example.com/hook', 'events' => ['comment_created'], 'secret' => ''],
    ]]);

    $ticket = Ticket::factory()->create(['submitter_id' => $submitter->id]);

    $listener = new DispatchWebhook();
    $listener->handle(new TicketCreated($ticket));

    Http::assertNothingSent();
});

it('does nothing when no webhooks configured', function () {
    Http::fake();

    $submitter = User::create(['name' => 'Sub', 'email' => 'sub4@test.com']);

    config(['helpdesk.webhooks' => []]);

    $ticket = Ticket::factory()->create(['submitter_id' => $submitter->id]);

    $listener = new DispatchWebhook();
    $listener->handle(new TicketCreated($ticket));

    Http::assertNothingSent();
});

it('sends webhook on comment created', function () {
    Http::fake();

    $submitter = User::create(['name' => 'Sub', 'email' => 'sub5@test.com']);

    config(['helpdesk.webhooks' => [
        ['url' => 'https://example.com/hook', 'events' => ['*'], 'secret' => ''],
    ]]);

    $ticket = Ticket::factory()->create(['submitter_id' => $submitter->id]);
    $comment = TicketComment::factory()->create([
        'ticket_id' => $ticket->id,
        'user_id' => $submitter->id,
    ]);

    $listener = new DispatchWebhook();
    $listener->handle(new CommentCreated($comment));

    Http::assertSent(function ($request) {
        return $request['event'] === 'comment_created';
    });
});
