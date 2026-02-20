<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Listeners;

use Crumbls\HelpDesk\Events\TicketAssigned;
use Crumbls\HelpDesk\Notifications\TicketAssignedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendTicketAssignedNotification implements ShouldQueue
{
    public function handle(TicketAssigned $event): void
    {
        $config = config('helpdesk.notifications', []);

        if (!($config['agent_ticket_assigned'] ?? true)) {
            return;
        }

        $event->user->notify(
            new TicketAssignedNotification($event->ticket, $event->role)
        );
    }
}
