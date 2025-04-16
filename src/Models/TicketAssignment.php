<?php

namespace Crumbls\HelpDesk\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class TicketAssignment extends Model
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
        return $this->belongsTo(User::class);
    }
}
