<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to an agent when a ticket is assigned to them.
 */
class TicketAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Model $ticket,
        protected string $role = 'assignee',
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $ticket = $this->ticket;

        return (new MailMessage())
            ->subject(__('helpdesk::notifications.ticket_assigned.subject', [
                'id' => $ticket->id,
                'title' => $ticket->title,
            ]))
            ->greeting(__('helpdesk::notifications.ticket_assigned.greeting', [
                'name' => $notifiable->name,
            ]))
            ->line(__('helpdesk::notifications.ticket_assigned.line1', [
                'title' => $ticket->title,
                'role' => $this->role,
            ]))
            ->line(__('helpdesk::notifications.ticket_assigned.line2', [
                'priority' => $ticket->priority?->name ?? 'None',
                'department' => $ticket->department?->name ?? 'Unassigned',
            ]));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'role' => $this->role,
            'type' => 'ticket_assigned',
        ];
    }
}
