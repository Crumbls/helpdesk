<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Models;

use Crumbls\HelpDesk\Contracts\Models\ActivityLogContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model implements ActivityLogContract
{
    public $timestamps = false;

    protected $table = 'helpdesk_activity_log';

    protected $fillable = [
        'ticket_id',
        'user_id',
        'type',
        'description',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(Models::user());
    }

    /**
     * Log an activity entry for a ticket.
     */
    public static function log(
        int $ticketId,
        string $type,
        string $description,
        ?int $userId = null,
        ?array $metadata = null,
    ): static {
        return static::create([
            'ticket_id' => $ticketId,
            'user_id' => $userId,
            'type' => $type,
            'description' => $description,
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }
}
