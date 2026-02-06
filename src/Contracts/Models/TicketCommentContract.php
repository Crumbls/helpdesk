<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Contracts\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface TicketCommentContract
{
    public function ticket(): BelongsTo;

    public function user(): BelongsTo;
}
