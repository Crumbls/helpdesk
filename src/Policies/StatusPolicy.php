<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Policies;

use Crumbls\HelpDesk\Contracts\Models\TicketStatusContract;
use Crumbls\HelpDesk\Contracts\Policies\StatusPolicyContract;
use Illuminate\Auth\Access\HandlesAuthorization;

class StatusPolicy implements StatusPolicyContract
{
    use HandlesAuthorization;

    public function viewAny($user): bool
    {
        return true;
    }

    public function view($user, TicketStatusContract $status): bool
    {
        return true;
    }

    public function create($user): bool
    {
        return true;
    }

    public function update($user, TicketStatusContract $status): bool
    {
        return true;
    }

    public function delete($user, TicketStatusContract $status): bool
    {
        return true;
    }

    public function restore($user, TicketStatusContract $status): bool
    {
        return true;
    }

    public function forceDelete($user, TicketStatusContract $status): bool
    {
        return false;
    }
}
