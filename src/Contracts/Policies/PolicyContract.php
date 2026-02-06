<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Contracts\Policies;

interface PolicyContract
{
    public function viewAny($user): bool;

    public function create($user): bool;
}
