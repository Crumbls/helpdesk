<?php

namespace Crumbls\HelpDesk\Models;

use Crumbls\HelpDesk\Contracts\Models\TicketAssignmentContract;
use Crumbls\HelpDesk\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketAssignment extends Model implements TicketAssignmentContract
{
    protected $table = 'helpdesk_ticket_assignments';

    protected $fillable = [
        'ticket_id',
        'user_id',
        'role',
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
