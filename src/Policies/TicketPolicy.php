<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Policies;

use Crumbls\HelpDesk\Contracts\Models\TicketContract;
use Crumbls\HelpDesk\Contracts\Policies\TicketPolicyContract;
use Illuminate\Auth\Access\HandlesAuthorization;

class TicketPolicy implements TicketPolicyContract
{
    use HandlesAuthorization;

    public function viewAny($user): bool
    {
        return true;
    }

    public function view($user, TicketContract $ticket): bool
    {
        return $ticket->submitter_id === $user->id
            || $ticket->assignees()->where('user_id', $user->id)->exists()
            || $ticket->watchers()->where('user_id', $user->id)->exists();
    }

    public function create($user): bool
    {
        return true;
    }

    public function update($user, TicketContract $ticket): bool
    {
        return $ticket->submitter_id === $user->id
            || $ticket->assignees()->where('user_id', $user->id)->exists();
    }

    public function delete($user, TicketContract $ticket): bool
    {
        return $ticket->submitter_id === $user->id;
    }

    public function restore($user, TicketContract $ticket): bool
    {
        return $ticket->submitter_id === $user->id;
    }

    public function forceDelete($user, TicketContract $ticket): bool
    {
        return false;
    }
}
