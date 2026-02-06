<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Events;

use Crumbls\HelpDesk\Models\TicketComment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public TicketComment $comment,
        public array $changes = []
    ) {}
}
