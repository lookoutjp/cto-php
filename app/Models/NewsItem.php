<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class NewsItem extends Model
{
    use BelongsToSite;

    protected $table = 'news';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'newsdate' => 'datetime',
        'adddatetime' => 'datetime',
        'editdatetime' => 'datetime',
    ];

    /** 公開日時が現在以前のものだけ（旧ASP: newsdate < now）。 */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('newsdate', '<=', now());
    }

    /** 一覧の並び順（旧ASP: istop desc, newsdate desc）。 */
    public function scopeListingOrder(Builder $query): Builder
    {
        return $query->orderByRaw("(istop = '1') desc")->orderByDesc('newsdate')->orderByDesc('id');
    }

    public function isPinned(): bool
    {
        return (string) $this->istop === '1';
    }
}
