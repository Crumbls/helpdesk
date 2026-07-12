<?php

use Crumbls\HelpDesk\Models\Ticket;
use Crumbls\HelpDesk\Models\TicketComment;
use Crumbls\HelpDesk\Models\TicketStatus;
use Crumbls\HelpDesk\Tests\Fixtures\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('auto closes stale tickets with a nullable system comment', function () {
    config([
        'helpdesk.auto_close.enabled' => true,
        'helpdesk.auto_close.days' => 14,
    ]);

    $submitter = User::query()->create([
        'name' => 'Submitter',
        'email' => 'submitter@example.com',
    ]);

    $openStatus = TicketStatus::factory()->create(['is_closed' => false]);
    $closedStatus = TicketStatus::factory()->closed()->create();

    config(['helpdesk.auto_close.closed_status_id' => $closedStatus->id]);

    $ticket = Ticket::factory()->create([
        'submitter_id' => $submitter->id,
        'ticket_status_id' => $openStatus->id,
        'updated_at' => now()->subDays(20),
        'closed_at' => null,
    ]);

    $this->artisan('helpdesk:auto-close')
        ->expectsOutput('Closed 1 stale ticket(s).')
        ->assertSuccessful();

    $ticket->refresh();

    expect($ticket->ticket_status_id)->toBe($closedStatus->id)
        ->and($ticket->closed_at)->not->toBeNull();

    $comment = TicketComment::query()->where('ticket_id', $ticket->id)->firstOrFail();

    expect($comment->user_id)->toBeNull()
        ->and($comment->content)->toBe('Ticket automatically closed due to inactivity.');
});
