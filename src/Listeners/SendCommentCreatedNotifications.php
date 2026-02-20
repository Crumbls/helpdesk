<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Listeners;

use Crumbls\HelpDesk\Events\CommentCreated;
use Crumbls\HelpDesk\Models;
use Crumbls\HelpDesk\Notifications\AdminNewComment;
use Crumbls\HelpDesk\Notifications\CommentReply;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendCommentCreatedNotifications implements ShouldQueue
{
    public function handle(CommentCreated $event): void
    {
        $comment = $event->comment;
        $ticket = $comment->ticket;
        $config = config('helpdesk.notifications', []);

        // Skip private/internal comments — don't notify submitter.
        if ($comment->is_private) {
            // Still notify admins about private comments if configured.
            if ($config['admin_new_comment'] ?? true) {
                $this->notifyAdmins(new AdminNewComment($comment), $comment->user_id);
            }
            return;
        }

        // Was this comment from the submitter or from an agent?
        $isFromSubmitter = $comment->user_id === $ticket->submitter_id;

        if ($isFromSubmitter) {
            // Guest replied — notify admins + assigned agents.
            if ($config['admin_new_comment'] ?? true) {
                $this->notifyAdmins(new AdminNewComment($comment));
                $this->notifyAssignees($ticket, new AdminNewComment($comment));
            }
        } else {
            // Agent replied — notify the submitter.
            if ($config['submitter_comment_reply'] ?? true) {
                $ticket->submitter?->notify(new CommentReply($comment));
            }

            // Notify other admins/assignees too (excluding the commenter).
            if ($config['admin_new_comment'] ?? true) {
                $this->notifyAdmins(new AdminNewComment($comment), $comment->user_id);
                $this->notifyAssignees($ticket, new AdminNewComment($comment), $comment->user_id);
            }
        }
    }

    protected function notifyAssignees($ticket, $notification, ?int $excludeUserId = null): void
    {
        $assignees = $ticket->assignees;

        foreach ($assignees as $assignee) {
            if ($excludeUserId && $assignee->id === $excludeUserId) {
                continue;
            }
            $assignee->notify($notification);
        }
    }

    protected function notifyAdmins($notification, ?int $excludeUserId = null): void
    {
        $userClass = Models::user();

        $adminIds = (array) config('helpdesk.notifications.admin_user_ids', []);
        $adminEmails = (array) config('helpdesk.notifications.admin_emails', []);

        $recipients = collect();

        if ($adminIds) {
            $recipients = $recipients->merge($userClass::whereIn('id', $adminIds)->get());
        }
        if ($adminEmails) {
            $recipients = $recipients->merge($userClass::whereIn('email', $adminEmails)->get());
        }

        $recipients->unique('id')->each(function ($user) use ($notification, $excludeUserId) {
            if ($excludeUserId && $user->id === $excludeUserId) {
                return;
            }
            $user->notify($notification);
        });
    }
}
