<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Contracts\Policies;

use Crumbls\HelpDesk\Contracts\Models\TicketStatusContract;

interface StatusPolicyContract extends PolicyContract
{
    public function view($user, TicketStatusContract $status): bool;

    public function update($user, TicketStatusContract $status): bool;

    public function delete($user, TicketStatusContract $status): bool;

    public function restore($user, TicketStatusContract $status): bool;

    public function forceDelete($user, TicketStatusContract $status): bool;
}
