<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Models;

use Crumbls\HelpDesk\Contracts\Models\SatisfactionRatingContract;
use Crumbls\HelpDesk\Database\Factories\SatisfactionRatingFactory;
use Crumbls\HelpDesk\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SatisfactionRating extends Model implements SatisfactionRatingContract
{
    use HasFactory;

    protected $table = 'helpdesk_satisfaction_ratings';

    protected $fillable = [
        'ticket_id',
        'user_id',
        'rating',
        'comment',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    protected static function newFactory()
    {
        return SatisfactionRatingFactory::new();
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(Models::user());
    }
}
