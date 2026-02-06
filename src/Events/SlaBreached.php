<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Events;

use Crumbls\HelpDesk\Models\Ticket;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SlaBreached
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public const TYPE_RESPONSE = 'response';
    public const TYPE_RESOLUTION = 'resolution';

    public function __construct(
        public Ticket $ticket,
        public string $type
    ) {}
}
