<?php

namespace Crumbls\HelpDesk\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Crumbls\HelpDesk\Traits\HasColors;

class Department extends Model
{
    use SoftDeletes, HasColors;

    protected $table = 'helpdesk_departments';

    protected $fillable = [
        'title',
        'description',
        'color_name',
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
        return $this->hasMany(Ticket::class, 'department_id');
    }
}
