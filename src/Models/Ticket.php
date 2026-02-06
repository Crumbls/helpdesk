<?php

namespace Crumbls\HelpDesk\Models;

use Crumbls\HelpDesk\Contracts\Models\TicketContract;
use Crumbls\HelpDesk\Database\Factories\TicketFactory;
use Crumbls\HelpDesk\Events\TicketCreated;
use Crumbls\HelpDesk\Events\TicketDeleted;
use Crumbls\HelpDesk\Events\TicketStatusChanged;
use Crumbls\HelpDesk\Events\TicketUpdated;
use Crumbls\HelpDesk\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model implements TicketContract
{
    use HasFactory,
        SoftDeletes;

    protected static function newFactory()
    {
        return TicketFactory::new();
    }

    protected static function booted(): void
    {
        static::created(function (Ticket $ticket) {
            if (config('helpdesk.events.enabled') && config('helpdesk.events.dispatch.ticket_created')) {
                event(new TicketCreated($ticket));
            }
        });

        static::updated(function (Ticket $ticket) {
            if (config('helpdesk.events.enabled') && config('helpdesk.events.dispatch.ticket_updated')) {
                event(new TicketUpdated($ticket, $ticket->getChanges()));
            }

            if (config('helpdesk.events.enabled')
                && config('helpdesk.events.dispatch.ticket_status_changed')
                && $ticket->wasChanged('ticket_status_id')
            ) {
                event(new TicketStatusChanged(
                    $ticket,
                    $ticket->getOriginal('ticket_status_id'),
                    $ticket->ticket_status_id
                ));
            }
        });

        static::deleting(function (Ticket $ticket) {
            if (config('helpdesk.events.enabled') && config('helpdesk.events.dispatch.ticket_deleted')) {
                event(new TicketDeleted($ticket));
            }
        });
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
        'first_response_at',
        'sla_response_due_at',
        'sla_resolution_due_at',
        'sla_response_breached',
        'sla_resolution_breached',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'closed_at' => 'datetime',
        'first_response_at' => 'datetime',
        'sla_response_due_at' => 'datetime',
        'sla_resolution_due_at' => 'datetime',
        'sla_response_breached' => 'boolean',
        'sla_resolution_breached' => 'boolean',
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
        return $this->belongsTo(Models::user(), 'submitter_id');
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
        return $this->belongsToMany(Models::user(), 'helpdesk_ticket_assignments')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function assignees(): BelongsToMany
    {
        return $this->assignedUsers()->wherePivot('role', 'assignee');
    }

    public function watchers(): BelongsToMany
    {
        return $this->belongsToMany(Models::user(), 'helpdesk_ticket_watchers');
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
