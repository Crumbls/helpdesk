<?php

namespace Crumbls\HelpDesk\Models;

use Crumbls\HelpDesk\Database\Factories\TicketStatusFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Crumbls\HelpDesk\Traits\HasColors;

class TicketStatus extends Model
{
    use HasFactory, SoftDeletes, HasColors;

    protected $table = 'helpdesk_ticket_statuses';

    protected $fillable = [
        'title',
        'description',
        'color_background',
        'color_foreground',
        'is_active',
        'is_default',
        'is_closed',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'is_closed' => 'boolean',
    ];

    protected $appends = [
        'background_color',
        'foreground_color',
        'color_scheme',
    ];

    protected static function newFactory(): TicketStatusFactory
    {
        return TicketStatusFactory::new();
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'ticket_status_id');
    }
}
