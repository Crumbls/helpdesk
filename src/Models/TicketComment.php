<?php

namespace Crumbls\HelpDesk\Models;

use Crumbls\HelpDesk\Contracts\Models\TicketCommentContract;
use Crumbls\HelpDesk\Database\Factories\TicketCommentFactory;
use Crumbls\HelpDesk\Events\CommentCreated;
use Crumbls\HelpDesk\Events\CommentDeleted;
use Crumbls\HelpDesk\Events\CommentUpdated;
use Crumbls\HelpDesk\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketComment extends Model implements TicketCommentContract
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

    protected static function booted(): void
    {
        static::created(function (TicketComment $comment) {
            if (config('helpdesk.events.enabled') && config('helpdesk.events.dispatch.comment_created')) {
                event(new CommentCreated($comment));
            }
        });

        static::updated(function (TicketComment $comment) {
            if (config('helpdesk.events.enabled') && config('helpdesk.events.dispatch.comment_updated')) {
                event(new CommentUpdated($comment, $comment->getChanges()));
            }
        });

        static::deleting(function (TicketComment $comment) {
            if (config('helpdesk.events.enabled') && config('helpdesk.events.dispatch.comment_deleted')) {
                event(new CommentDeleted($comment));
            }
        });
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(Models::user());
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Models::attachment(), 'attachable');
    }
}
