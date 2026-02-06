<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Contracts\Policies;

use Crumbls\HelpDesk\Contracts\Models\PriorityContract;

interface PriorityPolicyContract extends PolicyContract
{
    public function view($user, PriorityContract $priority): bool;

    public function update($user, PriorityContract $priority): bool;

    public function delete($user, PriorityContract $priority): bool;

    public function restore($user, PriorityContract $priority): bool;

    public function forceDelete($user, PriorityContract $priority): bool;
}
