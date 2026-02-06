<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Policies;

use Crumbls\HelpDesk\Contracts\Models\DepartmentContract;
use Crumbls\HelpDesk\Contracts\Policies\DepartmentPolicyContract;
use Illuminate\Auth\Access\HandlesAuthorization;

class DepartmentPolicy implements DepartmentPolicyContract
{
    use HandlesAuthorization;

    public function viewAny($user): bool
    {
        return true;
    }

    public function view($user, DepartmentContract $department): bool
    {
        return true;
    }

    public function create($user): bool
    {
        return true;
    }

    public function update($user, DepartmentContract $department): bool
    {
        return true;
    }

    public function delete($user, DepartmentContract $department): bool
    {
        return true;
    }

    public function restore($user, DepartmentContract $department): bool
    {
        return true;
    }

    public function forceDelete($user, DepartmentContract $department): bool
    {
        return false;
    }
}
