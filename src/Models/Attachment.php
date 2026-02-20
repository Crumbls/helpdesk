<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Models;

use Crumbls\HelpDesk\Contracts\Models\AttachmentContract;
use Crumbls\HelpDesk\Database\Factories\AttachmentFactory;
use Crumbls\HelpDesk\Models;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model implements AttachmentContract
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'helpdesk_attachments';

    protected $fillable = [
        'attachable_id',
        'attachable_type',
        'user_id',
        'filename',
        'original_filename',
        'mime_type',
        'size',
        'disk',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    protected static function newFactory()
    {
        return AttachmentFactory::new();
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(Models::user());
    }

    public function url(): Attribute
    {
        return Attribute::make(
            get: fn () => Storage::disk($this->disk)->url($this->filename)
        );
    }
}
