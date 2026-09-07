<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * カテゴリ管理員の割り当て・申請。1行 = ある会員がある content_sort の管理員（または申請中）。
 *   status: pending  … 会員が申請、スーパー管理員の承認待ち
 *           approved … 管理員（承認済み or スーパー管理員が直接指定）
 *
 * サイト横断で参照する（Member::managedCategoryIds 等）ため BelongsToSite は使わない。
 * サイトスコープが必要な箇所（Filament）は自前で site_id を絞る。
 */
class ContentSortManager extends Model
{
    protected $table = 'content_sort_managers';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'applied_at' => 'datetime',
        'decided_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ContentSort::class, 'content_sort_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id', 'member_id');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopePendingApplications(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
