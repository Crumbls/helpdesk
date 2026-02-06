<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Contracts\Policies;

use Crumbls\HelpDesk\Contracts\Models\TicketCommentContract;

interface CommentPolicyContract extends PolicyContract
{
    public function view($user, TicketCommentContract $comment): bool;

    public function update($user, TicketCommentContract $comment): bool;

    public function delete($user, TicketCommentContract $comment): bool;

    public function restore($user, TicketCommentContract $comment): bool;

    public function forceDelete($user, TicketCommentContract $comment): bool;
}
