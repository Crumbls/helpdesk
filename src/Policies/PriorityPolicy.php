<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Policies;

use Crumbls\HelpDesk\Contracts\Models\PriorityContract;
use Crumbls\HelpDesk\Contracts\Policies\PriorityPolicyContract;
use Illuminate\Auth\Access\HandlesAuthorization;

class PriorityPolicy implements PriorityPolicyContract
{
    use HandlesAuthorization;

    public function viewAny($user): bool
    {
        return true;
    }

    public function view($user, PriorityContract $priority): bool
    {
        return true;
    }

    public function create($user): bool
    {
        return true;
    }

    public function update($user, PriorityContract $priority): bool
    {
        return true;
    }

    public function delete($user, PriorityContract $priority): bool
    {
        return true;
    }

    public function restore($user, PriorityContract $priority): bool
    {
        return true;
    }

    public function forceDelete($user, PriorityContract $priority): bool
    {
        return false;
    }
}
