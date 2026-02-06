<?php

namespace Crumbls\HelpDesk\Models;

use Crumbls\HelpDesk\Contracts\Models\TicketTypeContract;
use Crumbls\HelpDesk\Database\Factories\TicketTypeFactory;
use Crumbls\HelpDesk\Traits\HasColors;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketType extends Model implements TicketTypeContract
{
    use HasFactory, SoftDeletes, HasColors;

    protected static function newFactory()
    {
        return TicketTypeFactory::new();
    }

    protected static function booted(): void
    {
        static::saving(function (TicketType $type) {
            if ($type->is_default) {
                static::where('is_default', true)
                    ->where('id', '!=', $type->id ?? 0)
                    ->update(['is_default' => false]);
            }
        });
    }

    protected $table = 'helpdesk_ticket_types';

    protected $fillable = [
        'title',
        'description',
        'color_background',
        'color_foreground',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
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
