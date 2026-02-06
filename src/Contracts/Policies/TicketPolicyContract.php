<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Contracts\Policies;

use Crumbls\HelpDesk\Contracts\Models\TicketContract;

interface TicketPolicyContract extends PolicyContract
{
    public function view($user, TicketContract $ticket): bool;

    public function update($user, TicketContract $ticket): bool;

    public function delete($user, TicketContract $ticket): bool;

    public function restore($user, TicketContract $ticket): bool;

    public function forceDelete($user, TicketContract $ticket): bool;
}
