<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Models;

use Crumbls\HelpDesk\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketRead extends Model
{
    protected $table = 'helpdesk_ticket_reads';

    protected $fillable = [
        'ticket_id',
        'user_id',
        'last_read_at',
    ];

    protected $casts = [
        'last_read_at' => 'datetime',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(Models::user());
    }
}
