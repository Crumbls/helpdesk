<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Contracts\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

interface TicketStatusContract
{
    public function tickets(): HasMany;
}
