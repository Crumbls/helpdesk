<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to the ticket submitter when an agent adds a public comment.
 */
class CommentReply extends Notification implements ShouldQueue
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
            ->subject(__('helpdesk::notifications.comment_reply.subject', [
                'id' => $ticket->id,
                'title' => $ticket->title,
            ]))
            ->greeting(__('helpdesk::notifications.comment_reply.greeting', [
                'name' => $notifiable->name,
            ]))
            ->line(__('helpdesk::notifications.comment_reply.line1', [
                'title' => $ticket->title,
            ]))
            ->line($comment->content);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->comment->ticket_id,
            'comment_id' => $this->comment->id,
            'type' => 'comment_reply',
        ];
    }
}
