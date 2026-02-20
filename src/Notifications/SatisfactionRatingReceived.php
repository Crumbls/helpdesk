<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Notifications;

use Crumbls\HelpDesk\Models\SatisfactionRating;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SatisfactionRatingReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected SatisfactionRating $rating,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $rating = $this->rating;
        $ticket = $rating->ticket;

        return (new MailMessage())
            ->subject(__('helpdesk::notifications.satisfaction_rating.subject', [
                'id' => $ticket->id,
                'title' => $ticket->title,
            ]))
            ->greeting(__('helpdesk::notifications.satisfaction_rating.greeting'))
            ->line(__('helpdesk::notifications.satisfaction_rating.line1', [
                'title' => $ticket->title,
                'rating' => $rating->rating,
                'user' => $rating->user?->name ?? 'User',
            ]))
            ->when($rating->comment, function ($mail) use ($rating) {
                return $mail->line(__('helpdesk::notifications.satisfaction_rating.comment', [
                    'comment' => $rating->comment,
                ]));
            });
    }

    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->rating->ticket_id,
            'rating' => $this->rating->rating,
            'type' => 'satisfaction_rating',
        ];
    }
}
