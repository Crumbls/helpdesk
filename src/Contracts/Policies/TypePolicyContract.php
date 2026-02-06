<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Contracts\Policies;

use Crumbls\HelpDesk\Contracts\Models\TicketTypeContract;

interface TypePolicyContract extends PolicyContract
{
    public function view($user, TicketTypeContract $type): bool;

    public function update($user, TicketTypeContract $type): bool;

    public function delete($user, TicketTypeContract $type): bool;

    public function restore($user, TicketTypeContract $type): bool;

    public function forceDelete($user, TicketTypeContract $type): bool;
}
