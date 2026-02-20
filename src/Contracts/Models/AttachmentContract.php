<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Contracts\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

interface AttachmentContract
{
    public function attachable(): MorphTo;

    public function user(): BelongsTo;
}
