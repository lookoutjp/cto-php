<?php

namespace App\Support;

use App\Models\Problem;
use App\Models\Risk;
use App\Models\RoutineWorkList;
use App\Models\StatusMaster;
use App\Models\Todo;
use App\Models\Wbs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Mypage（旧ASP Mypage.asp）の集計ロジック。
 *
 * 「私の」タスク = person_do が会員IDのもの（delete_to <> 1）。
 * status は statuses テーブル（kind 別、percent: 0=新規 / 100=完了 / -2=中止）。
 *
 * バケット（旧 CountRecord と同じ定義）:
 *   new       … status.percent = 0
 *   here(接近) … 期限が翌日〜4日後、かつ未完了/未中止
 *   late(遅延) … 期限が過去、かつ未完了/未中止
 *   nulldate  … 期限なし
 * 定例作業は duedate の代わりに actiondate、加えて today / tomorrow。
 */
class TaskDashboard
{
    /** @var array<string, class-string<\Illuminate\Database\Eloquent\Model>> */
    private const DATED_TYPES = [
        'todo' => Todo::class,
        'problem' => Problem::class,
        'risk' => Risk::class,
        'wbs' => Wbs::class,
    ];

    private const LABELS = [
        'todo' => 'TODO',
        'problem' => '課題',
        'risk' => 'リスク',
        'wbs' => 'WBS',
        'routinework' => '定例作業',
    ];

    public function __construct(private readonly string $memberId)
    {
    }

    public static function for(string $memberId): self
    {
        return new self($memberId);
    }

    /** 管理タスク対応状況グリッド（todo/problem/risk/wbs）。 */
    public function statusGrid(): array
    {
        $today = Carbon::today();
        $rows = [];

        foreach (self::DATED_TYPES as $kind => $model) {
            $openStatusIds = $this->openStatusIds($kind);
            $newStatusIds = $this->statusIdsByPercent($kind, 0);

            $base = fn (): Builder => $model::query()
                ->where('person_do', $this->memberId)
                ->where(fn ($q) => $q->where('delete_to', '!=', 1)->orWhereNull('delete_to'));

            $rows[$kind] = [
                'label' => self::LABELS[$kind],
                'view' => $kind,
                'new' => (clone $base())->whereIn('status', $newStatusIds)->count(),
                'here' => (clone $base())
                    ->whereNotNull('duedate')
                    ->whereBetween('duedate', [$today->copy()->addDay()->startOfDay(), $today->copy()->addDays(4)->endOfDay()])
                    ->whereIn('status', $openStatusIds)
                    ->count(),
                'late' => (clone $base())
                    ->whereNotNull('duedate')
                    ->where('duedate', '<', $today)
                    ->whereIn('status', $openStatusIds)
                    ->count(),
                'nulldate' => (clone $base())->whereNull('duedate')->count(),
            ];
        }

        return $rows;
    }

    /** 定例作業対応状況（actiondate ベース）。 */
    public function routineGrid(): array
    {
        $today = Carbon::today();
        $openStatusIds = $this->openStatusIds('routinework');
        $newStatusIds = $this->statusIdsByPercent('routinework', 0);

        $base = fn (): Builder => RoutineWorkList::query()
            ->where('person_do', $this->memberId)
            ->where(fn ($q) => $q->where('delete_to', '!=', 1)->orWhereNull('delete_to'));

        return [
            'label' => self::LABELS['routinework'],
            'new' => (clone $base())->whereIn('status', $newStatusIds)->count(),
            'here' => (clone $base())
                ->whereNotNull('actiondate')
                ->whereBetween('actiondate', [$today->copy()->addDay()->startOfDay(), $today->copy()->addDays(4)->endOfDay()])
                ->whereIn('status', $openStatusIds)
                ->count(),
            'late' => (clone $base())
                ->whereNotNull('actiondate')
                ->where('actiondate', '<', $today)
                ->whereIn('status', $openStatusIds)
                ->count(),
            'today' => (clone $base())->whereDate('actiondate', $today)->count(),
            'tomorrow' => (clone $base())->whereDate('actiondate', $today->copy()->addDay())->count(),
        ];
    }

    /** 「本日の計画作業」= dotoday が今日のもの。type => Collection<{id,title}> */
    public function todayTasks(): array
    {
        $today = Carbon::today();
        $result = [];

        foreach (self::DATED_TYPES + ['routinework' => RoutineWorkList::class] as $kind => $model) {
            $result[$kind] = [
                'label' => self::LABELS[$kind],
                'items' => $model::query()
                    ->where('person_do', $this->memberId)
                    ->whereDate('dotoday', $today)
                    ->orderByDesc('id')
                    ->get(['id', 'title']),
            ];
        }

        return $result;
    }

    public function hasAnyToday(): bool
    {
        return collect($this->todayTasks())->contains(fn ($t) => $t['items']->isNotEmpty());
    }

    private function statusIdsByPercent(string $kind, int $percent): Collection
    {
        return StatusMaster::query()
            ->whereRaw('lower(kind) = ?', [strtolower($kind)])
            ->where('percent', $percent)
            ->pluck('id');
    }

    /** 未完了(100)・未中止(-2) の status id。 */
    private function openStatusIds(string $kind): Collection
    {
        return StatusMaster::query()
            ->whereRaw('lower(kind) = ?', [strtolower($kind)])
            ->whereNotIn('percent', [100, -2])
            ->pluck('id');
    }
}
