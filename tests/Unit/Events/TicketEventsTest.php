<?php

use Crumbls\HelpDesk\Events\TicketCreated;
use Crumbls\HelpDesk\Events\TicketDeleted;
use Crumbls\HelpDesk\Events\TicketStatusChanged;
use Crumbls\HelpDesk\Events\TicketUpdated;
use Crumbls\HelpDesk\Models\Ticket;
use Crumbls\HelpDesk\Models\TicketStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

function createTicketEventTestUser(array $attributes = []): int
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
    config(['helpdesk.events.dispatch.ticket_created' => true]);
    config(['helpdesk.events.dispatch.ticket_updated' => true]);
    config(['helpdesk.events.dispatch.ticket_deleted' => true]);
    config(['helpdesk.events.dispatch.ticket_status_changed' => true]);
});

it('dispatches TicketCreated event when ticket is created', function () {
    Event::fake([TicketCreated::class]);

    $userId = createTicketEventTestUser();
    $ticket = Ticket::factory()->create(['submitter_id' => $userId]);

    Event::assertDispatched(TicketCreated::class, function ($event) use ($ticket) {
        return $event->ticket->id === $ticket->id;
    });
});

it('dispatches TicketUpdated event when ticket is updated', function () {
    Event::fake([TicketUpdated::class]);

    $userId = createTicketEventTestUser();
    $ticket = Ticket::factory()->create(['submitter_id' => $userId]);

    $ticket->update(['title' => 'Updated Title']);

    Event::assertDispatched(TicketUpdated::class, function ($event) use ($ticket) {
        return $event->ticket->id === $ticket->id
            && isset($event->changes['title']);
    });
});

it('dispatches TicketDeleted event when ticket is deleted', function () {
    Event::fake([TicketDeleted::class]);

    $userId = createTicketEventTestUser();
    $ticket = Ticket::factory()->create(['submitter_id' => $userId]);
    $ticketId = $ticket->id;

    $ticket->delete();

    Event::assertDispatched(TicketDeleted::class, function ($event) use ($ticketId) {
        return $event->ticket->id === $ticketId;
    });
});

it('dispatches TicketStatusChanged event when status changes', function () {
    Event::fake([TicketStatusChanged::class, TicketUpdated::class]);

    $userId = createTicketEventTestUser();
    $oldStatus = TicketStatus::factory()->create();
    $newStatus = TicketStatus::factory()->create();

    $ticket = Ticket::factory()->create([
        'submitter_id' => $userId,
        'ticket_status_id' => $oldStatus->id,
    ]);

    $ticket->update(['ticket_status_id' => $newStatus->id]);

    Event::assertDispatched(TicketStatusChanged::class, function ($event) use ($oldStatus, $newStatus) {
        return $event->oldStatus === $oldStatus->id
            && $event->newStatus === $newStatus->id;
    });
});

it('does not dispatch events when disabled in config', function () {
    config(['helpdesk.events.enabled' => false]);

    Event::fake([TicketCreated::class]);

    $userId = createTicketEventTestUser();
    Ticket::factory()->create(['submitter_id' => $userId]);

    Event::assertNotDispatched(TicketCreated::class);
});

it('does not dispatch specific event when that event is disabled', function () {
    config(['helpdesk.events.dispatch.ticket_created' => false]);

    Event::fake([TicketCreated::class]);

    $userId = createTicketEventTestUser();
    Ticket::factory()->create(['submitter_id' => $userId]);

    Event::assertNotDispatched(TicketCreated::class);
});

it('does not dispatch TicketStatusChanged when status unchanged', function () {
    Event::fake([TicketStatusChanged::class]);

    $userId = createTicketEventTestUser();
    $ticket = Ticket::factory()->create(['submitter_id' => $userId]);

    $ticket->update(['title' => 'New Title']);

    Event::assertNotDispatched(TicketStatusChanged::class);
});
