<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Content extends Model
{
    use BelongsToSite;

    protected $table = 'contents';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'adddatetime' => 'datetime',
        'createdt' => 'datetime',
        'edittime' => 'datetime',
    ];

    public function sort(): BelongsTo
    {
        return $this->belongsTo(ContentSort::class, 'content_sort');
    }

    /** 公開済み（承認済み = 旧ASP ok=1）。 */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('ok', 1);
    }

    public function scopeListingOrder(Builder $query): Builder
    {
        return $query->orderBy('junban')->orderByDesc('adddatetime')->orderByDesc('id');
    }
}
