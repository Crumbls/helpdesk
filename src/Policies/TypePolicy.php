<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Policies;

use Crumbls\HelpDesk\Contracts\Models\TicketTypeContract;
use Crumbls\HelpDesk\Contracts\Policies\TypePolicyContract;
use Illuminate\Auth\Access\HandlesAuthorization;

class TypePolicy implements TypePolicyContract
{
    use HandlesAuthorization;

    public function viewAny($user): bool
    {
        return true;
    }

    public function view($user, TicketTypeContract $type): bool
    {
        return true;
    }

    public function create($user): bool
    {
        return true;
    }

    public function update($user, TicketTypeContract $type): bool
    {
        return true;
    }

    public function delete($user, TicketTypeContract $type): bool
    {
        return true;
    }

    public function restore($user, TicketTypeContract $type): bool
    {
        return true;
    }

    public function forceDelete($user, TicketTypeContract $type): bool
    {
        return false;
    }
}
