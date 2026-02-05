<?php

namespace Crumbls\HelpDesk\Models;

use Crumbls\HelpDesk\Database\Factories\PriorityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Crumbls\HelpDesk\Traits\HasColors;

class CustomField extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'helpdesk_custom_fields';

    protected $fillable = [
       'title',
	    'notyetbuiltout'
    ];
/*
    protected static function newFactory(): PriorityFactory
    {
        return PriorityFactory::new();
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'priority_id');
    }
*/
}
