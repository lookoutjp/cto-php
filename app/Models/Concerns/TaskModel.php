<?php

namespace App\Models\Concerns;

use App\Models\Category;
use App\Models\Level;
use App\Models\Member;
use App\Models\StatusMaster;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * todo / problem / risk など、旧ASP で同一スキーマ・同一挙動だった
 * 「タスク系」テーブル共通の振る舞い。
 *
 * 使う側は `public static string $taskKind = 'todo';` を定義する
 * （statuses.kind / categories.kind の値）。
 */
trait TaskModel
{
    public function statusMaster(): BelongsTo
    {
        return $this->belongsTo(StatusMaster::class, 'status');
    }

    public function categoryModel(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'person_do', 'member_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'maker', 'member_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'team_id', 'level');
    }

    public function scopeNotDeleted(Builder $q): Builder
    {
        return $q->where(fn ($w) => $w->where('delete_to', '!=', 1)->orWhereNull('delete_to'));
    }

    public function scopeForMember(Builder $q, string $memberId): Builder
    {
        return $q->where('person_do', $memberId);
    }

    /**
     * Mypage / 旧 MakeSQLforListView の view フィルタ。
     * 例: mynew, mylate, myhere, mynulldate, myfinished, mydoing, myall,
     *     new, late, here, finished, all
     */
    public function scopeViewFilter(Builder $q, ?string $view, ?string $memberId = null): Builder
    {
        $view = strtolower((string) $view);
        if ($view === '') {
            return $q;
        }

        $kind = static::$taskKind;
        $today = Carbon::today();

        if (str_contains($view, 'my') && $memberId !== null) {
            $q->where('person_do', $memberId);
        }

        if (str_contains($view, 'new')) {
            $q->whereIn('status', static::statusIds($kind, fn ($s) => $s->where('percent', 0)));
        }
        if (str_contains($view, 'doing')) {
            $q->whereIn('status', static::statusIds($kind, fn ($s) => $s->whereNotIn('percent', [0, -2, 100])));
        }
        if (str_contains($view, 'here')) {
            $q->whereNotNull('duedate')
                ->whereBetween('duedate', [$today->copy()->addDay()->startOfDay(), $today->copy()->addDays(4)->endOfDay()])
                ->whereIn('status', static::statusIds($kind, fn ($s) => $s->whereNotIn('percent', [100, -2])));
        }
        if (str_contains($view, 'late')) {
            $q->whereNotNull('duedate')->where('duedate', '<', $today)
                ->whereIn('status', static::statusIds($kind, fn ($s) => $s->whereNotIn('percent', [100, -2])));
        }
        if (str_contains($view, 'finished')) {
            $q->whereIn('status', static::statusIds($kind, fn ($s) => $s->where('percent', 100)));
        }
        if (str_contains($view, 'nulldate')) {
            $q->whereNull('duedate');
        }

        return $q;
    }

    /** @return \Illuminate\Support\Collection<int, int> */
    protected static function statusIds(string $kind, callable $filter)
    {
        $q = StatusMaster::query()->whereRaw('lower(kind) = ?', [strtolower($kind)]);
        $filter($q);

        return $q->pluck('id');
    }

    public function isDone(): bool
    {
        return (int) optional($this->statusMaster)->percent === 100;
    }

    public function isOverdue(): bool
    {
        return $this->duedate !== null
            && $this->duedate->isPast()
            && ! $this->isDone();
    }
}
