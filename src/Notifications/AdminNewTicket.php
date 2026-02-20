<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminNewTicket extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Model $ticket,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $ticket = $this->ticket;

        return (new MailMessage())
            ->subject(__('helpdesk::notifications.admin_new_ticket.subject', [
                'id' => $ticket->id,
                'title' => $ticket->title,
            ]))
            ->greeting(__('helpdesk::notifications.admin_new_ticket.greeting'))
            ->line(__('helpdesk::notifications.admin_new_ticket.line1', [
                'title' => $ticket->title,
                'submitter' => $ticket->submitter?->name ?? 'Unknown',
                'email' => $ticket->submitter?->email ?? '',
            ]))
            ->line(__('helpdesk::notifications.admin_new_ticket.line2', [
                'department' => $ticket->department?->name ?? 'Unassigned',
                'priority' => $ticket->priority?->name ?? 'None',
                'source' => $ticket->source ?? 'api',
            ]))
            ->line($ticket->description);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'title' => $this->ticket->title,
            'type' => 'new_ticket',
        ];
    }
}
