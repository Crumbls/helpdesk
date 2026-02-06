<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Policies;

use Crumbls\HelpDesk\Contracts\Models\TicketCommentContract;
use Crumbls\HelpDesk\Contracts\Policies\CommentPolicyContract;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommentPolicy implements CommentPolicyContract
{
    use HandlesAuthorization;

    public function viewAny($user): bool
    {
        return true;
    }

    public function view($user, TicketCommentContract $comment): bool
    {
        if (!$comment->is_private) {
            return true;
        }

        return $comment->user_id === $user->id
            || $comment->ticket->assignees()->where('user_id', $user->id)->exists();
    }

    public function create($user): bool
    {
        return true;
    }

    public function update($user, TicketCommentContract $comment): bool
    {
        return $comment->user_id === $user->id;
    }

    public function delete($user, TicketCommentContract $comment): bool
    {
        return $comment->user_id === $user->id;
    }

    public function restore($user, TicketCommentContract $comment): bool
    {
        return $comment->user_id === $user->id;
    }

    public function forceDelete($user, TicketCommentContract $comment): bool
    {
        return false;
    }
}
