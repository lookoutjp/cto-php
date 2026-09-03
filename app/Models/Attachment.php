<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use App\Support\FileStorage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Attachment extends Model
{
    use BelongsToSite;

    protected $table = 'attachments';

    public const UPDATED_AT = null;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'size_bytes' => 'integer',
    ];

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id', 'member_id');
    }

    public function isImage(): bool
    {
        return FileStorage::isImage($this->ext);
    }

    public function isPdf(): bool
    {
        return FileStorage::isPdf($this->ext);
    }

    public function canPreviewInline(): bool
    {
        return FileStorage::canPreviewInline($this->ext);
    }

    public function downloadName(): string
    {
        return $this->original_name ?: ('attachment-'.$this->id.($this->ext ? '.'.$this->ext : ''));
    }

    public function humanSize(): string
    {
        return FileStorage::humanSize($this->size_bytes);
    }
}
