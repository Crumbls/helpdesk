<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GuestTicketLookup extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected string $listUrl,
        protected int $ticketCount,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject(__('helpdesk::notifications.ticket_lookup.subject'))
            ->greeting(__('helpdesk::notifications.ticket_lookup.greeting', [
                'name' => $notifiable->name,
            ]))
            ->line(__('helpdesk::notifications.ticket_lookup.line1', [
                'count' => $this->ticketCount,
            ]))
            ->action(__('helpdesk::notifications.ticket_lookup.action'), $this->listUrl)
            ->line(__('helpdesk::notifications.ticket_lookup.line2', [
                'minutes' => config('helpdesk.guest.link_expiry_minutes', 60),
            ]));
    }
}
