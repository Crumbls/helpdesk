<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Contracts\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

interface TicketContract
{
    public function type(): BelongsTo;

    public function status(): BelongsTo;

    public function submitter(): BelongsTo;

    public function department(): BelongsTo;

    public function priority(): BelongsTo;

    public function parentTicket(): BelongsTo;

    public function childTickets(): HasMany;

    public function assignments(): HasMany;

    public function assignedUsers(): BelongsToMany;

    public function assignees(): BelongsToMany;

    public function watchers(): BelongsToMany;

    public function comments(): HasMany;

    public function publicComments(): HasMany;

    public function privateComments(): HasMany;

    public function satisfactionRating(): HasOne;

    public function attachments(): MorphMany;
}
