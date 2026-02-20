<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Models;

use Crumbls\HelpDesk\Contracts\Models\CannedResponseContract;
use Crumbls\HelpDesk\Database\Factories\CannedResponseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class CannedResponse extends Model implements CannedResponseContract
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'helpdesk_canned_responses';

    protected $fillable = [
        'title',
        'content',
        'department_id',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function newFactory()
    {
        return CannedResponseFactory::new();
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForDepartment(Builder $query, ?int $departmentId): Builder
    {
        return $query->where(function ($q) use ($departmentId) {
            $q->where('department_id', $departmentId)
              ->orWhereNull('department_id');
        });
    }
}
