<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Events;

use Crumbls\HelpDesk\Models\Ticket;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public array $changes = []
    ) {}
}
