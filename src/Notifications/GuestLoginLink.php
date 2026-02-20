<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GuestLoginLink extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected string $loginUrl,
        protected Model $ticket,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject(__('helpdesk::notifications.guest_login.subject', [
                'ticket' => $this->ticket->title,
            ]))
            ->greeting(__('helpdesk::notifications.guest_login.greeting', [
                'name' => $notifiable->name,
            ]))
            ->line(__('helpdesk::notifications.guest_login.line1', [
                'ticket' => $this->ticket->title,
            ]))
            ->line(__('helpdesk::notifications.guest_login.line2'))
            ->action(__('helpdesk::notifications.guest_login.action'), $this->loginUrl)
            ->line(__('helpdesk::notifications.guest_login.line3', [
                'minutes' => config('helpdesk.guest.link_expiry_minutes', 60),
            ]));
    }
}
