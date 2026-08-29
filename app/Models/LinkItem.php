<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class LinkItem extends Model
{
    use BelongsToSite;

    protected $table = 'links';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'linktime' => 'datetime',
    ];

    /** 管理員が承認したリンク（旧 link.allow = 1）。 */
    public function scopeApproved(Builder $q): Builder
    {
        return $q->whereIn('allow', [1, '1']);
    }

    public function scopeListingOrder(Builder $q): Builder
    {
        return $q->orderByDesc('linktime')->orderByDesc('id');
    }
}
