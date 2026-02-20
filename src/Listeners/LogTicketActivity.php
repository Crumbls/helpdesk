<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Listeners;

use Crumbls\HelpDesk\Events\CommentCreated;
use Crumbls\HelpDesk\Events\SatisfactionRated;
use Crumbls\HelpDesk\Events\TicketAssigned;
use Crumbls\HelpDesk\Events\TicketCreated;
use Crumbls\HelpDesk\Events\TicketStatusChanged;
use Crumbls\HelpDesk\Models\ActivityLog;
use Crumbls\HelpDesk\Models;

class LogTicketActivity
{
    public function handle(object $event): void
    {
        if (!config('helpdesk.activity_log.enabled', true)) {
            return;
        }

        match (true) {
            $event instanceof TicketCreated => $this->logTicketCreated($event),
            $event instanceof TicketStatusChanged => $this->logStatusChanged($event),
            $event instanceof TicketAssigned => $this->logAssigned($event),
            $event instanceof CommentCreated => $this->logComment($event),
            $event instanceof SatisfactionRated => $this->logRated($event),
            default => null,
        };
    }

    protected function logTicketCreated(TicketCreated $event): void
    {
        $ticket = $event->ticket;

        ActivityLog::log(
            ticketId: $ticket->id,
            type: 'created',
            description: 'Ticket created',
            userId: $ticket->submitter_id,
            metadata: [
                'source' => $ticket->source,
                'department_id' => $ticket->department_id,
                'priority_id' => $ticket->priority_id,
            ],
        );
    }

    protected function logStatusChanged(TicketStatusChanged $event): void
    {
        $ticket = $event->ticket;
        $statusClass = Models::status();

        $oldName = $statusClass::find($event->oldStatus)?->title ?? 'Unknown';
        $newName = $statusClass::find($event->newStatus)?->title ?? 'Unknown';

        ActivityLog::log(
            ticketId: $ticket->id,
            type: 'status_changed',
            description: "Status changed from {$oldName} to {$newName}",
            userId: auth()->id(),
            metadata: [
                'old_status_id' => $event->oldStatus,
                'new_status_id' => $event->newStatus,
                'old_status_name' => $oldName,
                'new_status_name' => $newName,
            ],
        );
    }

    protected function logAssigned(TicketAssigned $event): void
    {
        $user = $event->user;
        $name = is_object($user) ? $user->name : "User #{$user}";

        ActivityLog::log(
            ticketId: $event->ticket->id,
            type: 'assigned',
            description: "Assigned to {$name} as {$event->role}",
            userId: auth()->id(),
            metadata: [
                'assigned_user_id' => is_object($user) ? $user->id : $user,
                'role' => $event->role,
            ],
        );
    }

    protected function logComment(CommentCreated $event): void
    {
        $comment = $event->comment;
        $user = $comment->user;

        ActivityLog::log(
            ticketId: $comment->ticket_id,
            type: $comment->is_private ? 'internal_note' : 'commented',
            description: $comment->is_private
                ? ($user?->name ?? 'Someone') . ' added an internal note'
                : ($user?->name ?? 'Someone') . ' replied',
            userId: $comment->user_id,
            metadata: [
                'comment_id' => $comment->id,
                'is_private' => $comment->is_private,
            ],
        );
    }

    protected function logRated(SatisfactionRated $event): void
    {
        ActivityLog::log(
            ticketId: $event->ticket->id,
            type: 'rated',
            description: "Customer rated {$event->rating->rating}/5",
            userId: $event->rating->user_id,
            metadata: [
                'rating' => $event->rating->rating,
                'comment' => $event->rating->comment,
            ],
        );
    }
}
