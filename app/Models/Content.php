<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use App\Models\Concerns\HasAttachments;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Content extends Model
{
    use BelongsToSite;
    use HasAttachments;

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

    /** 投稿者（会員投稿の場合。owner と同じことが多い）。 */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id', 'member_id');
    }

    /** 公開済み（承認済み = 旧ASP ok=1）。 */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('ok', 1);
    }

    /** 会員が投稿し、カテゴリ管理員の承認待ちのもの（ok=2）。 */
    public function scopePendingReview(Builder $query): Builder
    {
        return $query->where('ok', 2);
    }

    public function scopeListingOrder(Builder $query): Builder
    {
        return $query->orderBy('junban')->orderByDesc('adddatetime')->orderByDesc('id');
    }

    /** おすすめ（旧ASP recommend = 1）。 */
    public function scopeRecommended(Builder $query): Builder
    {
        return $query->where('recommend', 1);
    }

    /** 公開カテゴリ（ninshou null/0）に属する公開済みコンテンツだけに絞る。 */
    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->published()
            ->whereIn('content_sort', ContentSort::query()->publicVisible()->select('id'));
    }
}
