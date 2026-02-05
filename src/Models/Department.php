<?php

namespace Crumbls\HelpDesk\Models;

use Crumbls\HelpDesk\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Crumbls\HelpDesk\Database\Factories\DepartmentFactory;
use Crumbls\HelpDesk\Traits\HasColors;

class Department extends Model
{
    use HasFactory, SoftDeletes, HasColors;

    protected static function newFactory()
    {
        return DepartmentFactory::new();
    }

    protected $table = 'helpdesk_departments';

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
        return $this->hasMany(Models::ticket(), 'department_id');
    }

	public function users(): BelongsToMany
	{
		dd(__LINE__);
		return $this->belongsToMany(Models::user(), 'helpdesk_department_user')
			->using(DepartmentUser::class)
			->withPivot(['role', 'assigned_only'])
			->withTimestamps();
	}
}
