<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContentSort extends Model
{
    use BelongsToSite;

    protected $table = 'content_sorts';
    public $timestamps = false;
    protected $guarded = [];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'father_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'father_id');
    }

    public function contents(): HasMany
    {
        return $this->hasMany(Content::class, 'content_sort');
    }
}
