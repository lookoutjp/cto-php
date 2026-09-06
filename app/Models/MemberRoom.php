<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Scope;

/**
 * 会員 × サイト（テナント）の所属・権限。旧ASP: member_room。
 *   ninshou     … -1=管理員 / 1=参加者 / 0=コンテンツ閲覧のみ / NULL=加入申請中（未承認）
 *   applied_at  … 加入申請日時（既存会員の他サイト加入申請）
 *   approved_at … 承認日時
 *
 * 承認待ち行はデフォルトのクエリから除外される（confirmed グローバルスコープ）。
 * 承認待ちを扱う箇所（申請フロー・Filament の承認画面）は withoutGlobalScope() する。
 */
class MemberRoom extends Model
{
    protected $table = 'member_room';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'applied_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('confirmed', new class implements Scope
        {
            public function apply(Builder $builder, Model $model): void
            {
                $builder->where(function (Builder $q) {
                    $q->whereNull('member_room.applied_at')
                        ->orWhereNotNull('member_room.approved_at');
                });
            }
        });
    }

    /** 承認待ちの加入申請だけ（グローバルスコープを外したクエリで使う）。 */
    public function scopePendingRequests(Builder $query): Builder
    {
        return $query->whereNotNull('applied_at')->whereNull('approved_at');
    }

    public function isPending(): bool
    {
        return $this->applied_at !== null && $this->approved_at === null;
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id', 'member_id');
    }

    /** ninshou の表示ラベル。 */
    public function ninshouLabel(): string
    {
        if ($this->ninshou === null) {
            return $this->isPending() ? '承認待ち' : '—';
        }

        return match ((int) $this->ninshou) {
            -1 => '管理員',
            1 => '参加者',
            0 => '閲覧のみ',
            default => (string) $this->ninshou,
        };
    }
}
