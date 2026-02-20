<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Listeners;

use Crumbls\HelpDesk\Events\TicketStatusChanged;
use Crumbls\HelpDesk\Notifications\TicketStatusUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendTicketStatusChangedNotifications implements ShouldQueue
{
    public function handle(TicketStatusChanged $event): void
    {
        $config = config('helpdesk.notifications', []);

        if (!($config['submitter_status_changed'] ?? true)) {
            return;
        }

        $ticket = $event->ticket;

        $ticket->submitter?->notify(
            new TicketStatusUpdated($ticket, $event->oldStatus, $event->newStatus)
        );
    }
}
