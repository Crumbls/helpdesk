<?php

namespace Crumbls\HelpDesk\Models;

use Crumbls\HelpDesk\Contracts\Models\TicketContract;
use Crumbls\HelpDesk\Contracts\SlaCalculator;
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
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
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
        static::creating(function (Ticket $ticket) {
            // Generate reference number.
            if (empty($ticket->reference)) {
                $prefix = config('helpdesk.reference.prefix', 'HD');
                $pad = config('helpdesk.reference.pad', 5);
                $lastId = (int) static::withTrashed()->max('id');
                $ticket->reference = $prefix . '-' . str_pad((string) ($lastId + 1), $pad, '0', STR_PAD_LEFT);
            }

            if (empty($ticket->ticket_status_id)) {
                $statusClass = Models::status();
                $default = $statusClass::where('is_default', true)->first();
                if ($default) {
                    $ticket->ticket_status_id = $default->id;
                }
            }

            if (empty($ticket->ticket_type_id)) {
                $typeClass = Models::type();
                $default = $typeClass::where('is_default', true)->first();
                if ($default) {
                    $ticket->ticket_type_id = $default->id;
                }
            }

            if (config('helpdesk.sla.enabled') && $ticket->priority_id) {
                $sla = app(SlaCalculator::class);
                $now = now();

                if (empty($ticket->sla_response_due_at)) {
                    $ticket->sla_response_due_at = $sla->calculateResponseDue($ticket->priority_id, $now);
                }

                if (empty($ticket->sla_resolution_due_at)) {
                    $ticket->sla_resolution_due_at = $sla->calculateResolutionDue($ticket->priority_id, $now);
                }
            }
        });

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
        'reference',
        'ticket_type_id',
        'ticket_status_id',
        'submitter_id',
        'submitter_name',
        'submitter_email',
        'submitter_phone',
        'submitter_company',
        'department_id',
        'priority_id',
        'parent_ticket_id',
        'merged_into_ticket_id',
        'merged_at',
        'title',
        'description',
        'resolution',
        'source',
        'metadata',
        'due_at',
        'closed_at',
        'first_response_at',
        'sla_response_due_at',
        'sla_resolution_due_at',
        'sla_response_breached',
        'sla_resolution_breached',
    ];

    protected $casts = [
        'metadata' => 'array',
        'due_at' => 'datetime',
        'closed_at' => 'datetime',
        'merged_at' => 'datetime',
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

    public function satisfactionRating(): HasOne
    {
        return $this->hasOne(SatisfactionRating::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Models::attachment(), 'attachable');
    }

    public function activityLog(): HasMany
    {
        return $this->hasMany(ActivityLog::class)->orderBy('created_at');
    }

    public function mergedInto(): BelongsTo
    {
        return $this->belongsTo(self::class, 'merged_into_ticket_id');
    }

    public function mergedTickets(): HasMany
    {
        return $this->hasMany(self::class, 'merged_into_ticket_id');
    }

    /**
     * Check if this ticket has been merged into another.
     */
    public function isMerged(): bool
    {
        return $this->merged_into_ticket_id !== null;
    }

    /**
     * Merge this ticket into a target ticket.
     * Moves comments, updates status, logs the activity.
     */
    public function mergeInto(self $target): void
    {
        $commentClass = Models::comment();

        // Move all comments to the target ticket.
        $commentClass::where('ticket_id', $this->id)->update(['ticket_id' => $target->id]);

        // Move attachments to target.
        $attachmentClass = Models::attachment();
        $attachmentClass::where('attachable_type', static::class)
            ->where('attachable_id', $this->id)
            ->update(['attachable_id' => $target->id]);

        // Mark this ticket as merged.
        $this->update([
            'merged_into_ticket_id' => $target->id,
            'merged_at' => now(),
        ]);

        // Log on both tickets.
        ActivityLog::log(
            ticketId: $this->id,
            type: 'merged',
            description: "Merged into {$target->reference}",
            userId: auth()->id(),
            metadata: ['target_ticket_id' => $target->id, 'target_reference' => $target->reference],
        );

        ActivityLog::log(
            ticketId: $target->id,
            type: 'merged',
            description: "Ticket {$this->reference} merged into this ticket",
            userId: auth()->id(),
            metadata: ['source_ticket_id' => $this->id, 'source_reference' => $this->reference],
        );
    }
}
