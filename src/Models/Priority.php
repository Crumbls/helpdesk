<?php

namespace Crumbls\HelpDesk\Models;

use Crumbls\HelpDesk\Contracts\Models\PriorityContract;
use Crumbls\HelpDesk\Database\Factories\PriorityFactory;
use Crumbls\HelpDesk\Traits\HasColors;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Priority extends Model implements PriorityContract
{
    use HasFactory, SoftDeletes, HasColors;

    protected $table = 'helpdesk_priorities';

    protected $fillable = [
        'title',
        'description',
        'color_background',
        'color_foreground',
        'level',
        'is_active',
        'is_default',
        'sla_response_hours',
        'sla_resolution_hours',
    ];

    protected $casts = [
        'level' => 'integer',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'sla_response_hours' => 'integer',
        'sla_resolution_hours' => 'integer',
    ];

    protected $appends = [
        'background_color',
        'foreground_color',
        'color_scheme',
    ];

    protected static function newFactory(): PriorityFactory
    {
        return PriorityFactory::new();
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'priority_id');
    }
}
