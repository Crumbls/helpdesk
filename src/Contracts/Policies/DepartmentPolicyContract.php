<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Contracts\Policies;

use Crumbls\HelpDesk\Contracts\Models\DepartmentContract;

interface DepartmentPolicyContract extends PolicyContract
{
    public function view($user, DepartmentContract $department): bool;

    public function update($user, DepartmentContract $department): bool;

    public function delete($user, DepartmentContract $department): bool;

    public function restore($user, DepartmentContract $department): bool;

    public function forceDelete($user, DepartmentContract $department): bool;
}
