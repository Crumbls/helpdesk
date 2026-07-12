<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class DepartmentUser extends Pivot
{
    protected $table = 'helpdesk_department_user';

    protected $fillable = [
        'department_id',
        'user_id',
        'role',
        'assigned_only',
    ];

    protected $casts = [
        'assigned_only' => 'boolean',
    ];
}
