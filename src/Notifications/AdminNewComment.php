<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminNewComment extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Model $comment,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $comment = $this->comment;
        $ticket = $comment->ticket;

        return (new MailMessage())
            ->subject(__('helpdesk::notifications.admin_new_comment.subject', [
                'id' => $ticket->id,
                'title' => $ticket->title,
            ]))
            ->greeting(__('helpdesk::notifications.admin_new_comment.greeting'))
            ->line(__('helpdesk::notifications.admin_new_comment.line1', [
                'user' => $comment->user?->name ?? 'Unknown',
                'title' => $ticket->title,
            ]))
            ->line($comment->content);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->comment->ticket_id,
            'comment_id' => $this->comment->id,
            'type' => 'new_comment',
        ];
    }
}
