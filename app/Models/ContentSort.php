<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContentSort extends Model
{
    use BelongsToSite;

    protected $table = 'content_sorts';
    public $timestamps = false;
    protected $guarded = [];

    /**
     * 未ログインの来訪者に見せてよいカテゴリ（旧ASP: ninshou is null or 0）。
     */
    public function scopePublicVisible(Builder $query): Builder
    {
        return $query->where(fn ($q) => $q->whereNull('ninshou')->orWhere('ninshou', 0));
    }

    public function scopeListingOrder(Builder $query): Builder
    {
        return $query->orderBy('junban')->orderBy('id');
    }

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
