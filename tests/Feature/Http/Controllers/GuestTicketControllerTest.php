<?php

use Crumbls\HelpDesk\Models\Ticket;
use Crumbls\HelpDesk\Models\TicketStatus;
use Crumbls\HelpDesk\Models\TicketType;
use Crumbls\HelpDesk\Models\SatisfactionRating;
use Crumbls\HelpDesk\Notifications\GuestLoginLink;
use Crumbls\HelpDesk\Notifications\GuestTicketLookup;
use Crumbls\HelpDesk\Tests\Fixtures\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

uses(RefreshDatabase::class);

function guestDeps(): array
{
    $status = TicketStatus::factory()->default()->create();
    $type = TicketType::factory()->create();
    return compact('status', 'type');
}

// -- Store (Guest Ticket Creation) --

it('creates a ticket and user for new guest', function () {
    Notification::fake();
    $deps = guestDeps();

    $response = $this->postJson('/helpdesk/tickets', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'title' => 'Help me',
        'description' => 'Something is broken',
        'ticket_type_id' => $deps['type']->id,
    ]);

    $response->assertStatus(201);
    $response->assertJsonPath('logged_in', true);
    $response->assertJsonPath('ticket.title', 'Help me');

    $this->assertDatabaseHas('users', ['email' => 'jane@example.com']);
    $this->assertDatabaseHas('helpdesk_tickets', ['title' => 'Help me', 'source' => 'guest']);
});

it('logs in existing user with zero tickets', function () {
    Notification::fake();
    $deps = guestDeps();
    $user = User::create(['name' => 'Existing', 'email' => 'existing@example.com']);

    $response = $this->postJson('/helpdesk/tickets', [
        'name' => 'Existing',
        'email' => 'existing@example.com',
        'title' => 'First ticket',
        'description' => 'Details here',
        'ticket_type_id' => $deps['type']->id,
    ]);

    $response->assertStatus(201);
    $response->assertJsonPath('logged_in', true);
});

it('does not log in existing user who has tickets', function () {
    Notification::fake();
    $deps = guestDeps();
    $user = User::create(['name' => 'Returning', 'email' => 'returning@example.com']);
    Ticket::factory()->create(['submitter_id' => $user->id]);

    $response = $this->postJson('/helpdesk/tickets', [
        'name' => 'Returning',
        'email' => 'returning@example.com',
        'title' => 'Second ticket',
        'description' => 'More details',
        'ticket_type_id' => $deps['type']->id,
    ]);

    $response->assertStatus(201);
    $response->assertJsonPath('logged_in', false);

    Notification::assertSentTo($user, GuestLoginLink::class);
});

it('captures lead metadata on guest ticket', function () {
    Notification::fake();
    $deps = guestDeps();

    $response = $this->postJson('/helpdesk/tickets', [
        'name' => 'Lead',
        'email' => 'lead@example.com',
        'title' => 'Interested',
        'description' => 'Tell me more',
        'phone' => '555-1234',
        'company' => 'Acme Inc',
        'ticket_type_id' => $deps['type']->id,
        'utm_source' => 'google',
        'utm_medium' => 'cpc',
        'referrer' => 'https://google.com',
    ]);

    $response->assertStatus(201);

    $ticket = Ticket::where('submitter_email', 'lead@example.com')->first();
    expect($ticket->submitter_phone)->toBe('555-1234');
    expect($ticket->submitter_company)->toBe('Acme Inc');
    expect($ticket->metadata['utm_source'])->toBe('google');
});

it('validates required fields on guest store', function () {
    $response = $this->postJson('/helpdesk/tickets', []);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['name', 'email', 'title', 'description']);
});

// -- Lookup --

it('returns success on lookup regardless of email existence', function () {
    Notification::fake();

    $response = $this->postJson('/helpdesk/lookup', ['email' => 'nobody@example.com']);
    $response->assertOk();
    $response->assertJsonFragment(['message' => 'If tickets exist for that email, a link has been sent.']);

    // No users exist, so nothing could have been sent
    Notification::assertNothingSent();
});

it('sends lookup notification when user has tickets', function () {
    Notification::fake();
    $user = User::create(['name' => 'Has Tickets', 'email' => 'has@example.com']);
    Ticket::factory()->create(['submitter_id' => $user->id]);

    $response = $this->postJson('/helpdesk/lookup', ['email' => 'has@example.com']);
    $response->assertOk();

    Notification::assertSentTo($user, GuestTicketLookup::class);
});

// -- Signed URL: List Tickets --

it('lists tickets via signed url', function () {
    $user = User::create(['name' => 'Signer', 'email' => 'signer@example.com']);
    Ticket::factory()->count(3)->create(['submitter_id' => $user->id]);

    $url = URL::temporarySignedRoute('helpdesk.guest.tickets', now()->addHour(), ['user' => $user->id]);

    $response = $this->getJson($url);
    $response->assertOk();
    $response->assertJsonCount(3, 'data');
});

it('rejects unsigned request to list tickets', function () {
    $response = $this->getJson('/helpdesk/tickets/1');
    $response->assertStatus(403);
});

// -- Signed URL: Show Ticket --

it('shows ticket via signed url', function () {
    $user = User::create(['name' => 'Viewer', 'email' => 'viewer@example.com']);
    $ticket = Ticket::factory()->create(['submitter_id' => $user->id, 'title' => 'Viewable']);

    $url = URL::temporarySignedRoute('helpdesk.guest.ticket.show', now()->addHour(), [
        'ticket' => $ticket->id,
        'user' => $user->id,
    ]);

    $response = $this->getJson($url);
    $response->assertOk();
    $response->assertJsonFragment(['title' => 'Viewable']);
});

it('returns 404 when viewing ticket belonging to different user', function () {
    $user = User::create(['name' => 'Owner', 'email' => 'owner@example.com']);
    $other = User::create(['name' => 'Other', 'email' => 'other@example.com']);
    $ticket = Ticket::factory()->create(['submitter_id' => $user->id]);

    $url = URL::temporarySignedRoute('helpdesk.guest.ticket.show', now()->addHour(), [
        'ticket' => $ticket->id,
        'user' => $other->id,
    ]);

    $response = $this->getJson($url);
    $response->assertNotFound();
});

// -- Signed URL: Add Comment --

it('adds comment via signed url', function () {
    $user = User::create(['name' => 'Commenter', 'email' => 'commenter@example.com']);
    $ticket = Ticket::factory()->create(['submitter_id' => $user->id]);

    $url = URL::temporarySignedRoute('helpdesk.guest.ticket.comment', now()->addHour(), [
        'ticket' => $ticket->id,
        'user' => $user->id,
    ]);

    $response = $this->postJson($url, ['content' => 'Here is my reply']);
    $response->assertStatus(201);

    $this->assertDatabaseHas('helpdesk_ticket_comments', [
        'ticket_id' => $ticket->id,
        'content' => 'Here is my reply',
        'is_private' => false,
    ]);
});

// -- Signed URL: Rate Ticket --

it('rates a closed ticket', function () {
    $user = User::create(['name' => 'Rater', 'email' => 'rater@example.com']);
    $ticket = Ticket::factory()->closed()->create(['submitter_id' => $user->id]);

    $url = URL::temporarySignedRoute('helpdesk.guest.ticket.rate', now()->addHour(), [
        'ticket' => $ticket->id,
        'user' => $user->id,
    ]);

    $response = $this->postJson($url, ['rating' => 5, 'comment' => 'Great support!']);
    $response->assertStatus(201);
    $response->assertJsonFragment(['rating' => 5]);
});

it('rejects rating on open ticket', function () {
    $user = User::create(['name' => 'Rater', 'email' => 'rater2@example.com']);
    $ticket = Ticket::factory()->create(['submitter_id' => $user->id, 'closed_at' => null]);

    $url = URL::temporarySignedRoute('helpdesk.guest.ticket.rate', now()->addHour(), [
        'ticket' => $ticket->id,
        'user' => $user->id,
    ]);

    $response = $this->postJson($url, ['rating' => 3]);
    $response->assertStatus(422);
    $response->assertJsonPath('error.message', 'Ticket must be closed before rating');
});

it('rejects duplicate rating', function () {
    $user = User::create(['name' => 'Rater', 'email' => 'rater3@example.com']);
    $ticket = Ticket::factory()->closed()->create(['submitter_id' => $user->id]);

    SatisfactionRating::create([
        'ticket_id' => $ticket->id,
        'user_id' => $user->id,
        'rating' => 4,
    ]);

    $url = URL::temporarySignedRoute('helpdesk.guest.ticket.rate', now()->addHour(), [
        'ticket' => $ticket->id,
        'user' => $user->id,
    ]);

    $response = $this->postJson($url, ['rating' => 5]);
    $response->assertStatus(422);
    $response->assertJsonPath('error.message', 'This ticket has already been rated');
});

// -- Login --

it('logs in user via signed login link', function () {
    $user = User::create(['name' => 'Logger', 'email' => 'logger@example.com']);

    $url = URL::temporarySignedRoute('helpdesk.guest.login', now()->addHour(), ['user' => $user->id]);

    $response = $this->get($url);
    $response->assertRedirect();

    expect(Auth::id())->toBe($user->id);
});
