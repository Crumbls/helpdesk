<?php

namespace Crumbls\HelpDesk\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\User;
use Crumbls\HelpDesk\Database\Factories\TicketFactory;

class Ticket extends Model
{
    use HasFactory,
	    SoftDeletes;

    protected static function newFactory()
    {
        return TicketFactory::new();
    }

    protected $table = 'helpdesk_tickets';

    protected $fillable = [
        'ticket_type_id',
        'ticket_status_id',
        'submitter_id',
        'department_id',
        'priority_id',
        'parent_ticket_id',
        'title',
        'description',
        'resolution',
        'source',
        'due_at',
        'closed_at',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(TicketType::class, 'ticket_type_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(TicketStatus::class, 'ticket_status_id');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitter_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(Priority::class);
    }

    public function parentTicket(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_ticket_id');
    }

    public function childTickets(): HasMany
    {
        return $this->hasMany(self::class, 'parent_ticket_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(TicketAssignment::class);
    }

    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'helpdesk_ticket_assignments')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function assignees(): BelongsToMany
    {
        return $this->assignedUsers()->wherePivot('role', 'assignee');
    }

    public function watchers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'helpdesk_ticket_watchers');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TicketComment::class);
    }

    public function publicComments(): HasMany
    {
        return $this->hasMany(TicketComment::class)->where('is_private', false);
    }

    public function privateComments(): HasMany
    {
        return $this->hasMany(TicketComment::class)->where('is_private', true);
    }
}
