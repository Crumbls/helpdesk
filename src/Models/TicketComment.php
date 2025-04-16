<?php

namespace Crumbls\HelpDesk\Models;

use App\Models\User;
use Crumbls\HelpDesk\Database\Factories\TicketCommentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketComment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'helpdesk_ticket_comments';

    protected $fillable = [
        'ticket_id',
        'user_id',
        'content',
        'is_private',
        'is_resolution',
    ];

    protected $casts = [
        'is_private' => 'boolean',
        'is_resolution' => 'boolean',
    ];

    protected static function newFactory()
    {
        return TicketCommentFactory::new();
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
