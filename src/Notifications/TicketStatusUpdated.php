<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Notifications;

use Crumbls\HelpDesk\Models;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to the ticket submitter when the ticket status changes.
 */
class TicketStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Model $ticket,
        protected mixed $oldStatusId,
        protected mixed $newStatusId,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $ticket = $this->ticket;
        $statusClass = Models::status();
        $oldStatus = $statusClass::find($this->oldStatusId);
        $newStatus = $statusClass::find($this->newStatusId);

        return (new MailMessage())
            ->subject(__('helpdesk::notifications.ticket_status_updated.subject', [
                'id' => $ticket->id,
                'status' => $newStatus?->name ?? 'Unknown',
            ]))
            ->greeting(__('helpdesk::notifications.ticket_status_updated.greeting', [
                'name' => $notifiable->name,
            ]))
            ->line(__('helpdesk::notifications.ticket_status_updated.line1', [
                'title' => $ticket->title,
                'old' => $oldStatus?->name ?? 'Unknown',
                'new' => $newStatus?->name ?? 'Unknown',
            ]));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'old_status_id' => $this->oldStatusId,
            'new_status_id' => $this->newStatusId,
            'type' => 'status_updated',
        ];
    }
}
