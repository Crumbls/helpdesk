<?php

namespace Crumbls\HelpDesk\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Crumbls\HelpDesk\Database\Factories\TicketTypeFactory;
use Crumbls\HelpDesk\Traits\HasColors;

class TicketType extends Model
{
    use HasFactory, SoftDeletes, HasColors;

    protected static function newFactory()
    {
        return TicketTypeFactory::new();
    }

    protected $table = 'helpdesk_ticket_types';

    protected $fillable = [
        'title',
        'description',
        'color_background',
        'color_foreground',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'background_color',
        'foreground_color',
        'color_scheme',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
