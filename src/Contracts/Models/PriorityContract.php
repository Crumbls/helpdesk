<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Contracts\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

interface PriorityContract
{
    public function tickets(): HasMany;
}
