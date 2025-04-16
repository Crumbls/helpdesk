<?php

namespace Crumbls\HelpDesk\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Crumbls\HelpDesk\Traits\HasColors;

class Priority extends Model
{
    use SoftDeletes, HasColors;

    protected $table = 'helpdesk_priorities';

    protected $fillable = [
        'title',
        'description',
        'color_name',
        'color_background',
        'color_foreground',
        'level',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'level' => 'integer',
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
        return $this->hasMany(Ticket::class, 'priority_id');
    }
}
