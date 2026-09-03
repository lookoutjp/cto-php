<?php

namespace App\Support;

use App\Models\Relation;
use App\Models\Wbs;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * WBS の簡易スケジューリング（CPM: Critical Path Method）。
 *
 * - 依存タイプ: FS / SS / FF / SF（relations.dep_type）。ラグ: relations.lag_days（負=リード）。
 * - 期間・ラグは WorkCalendar に従う（'working' = 土日+休日除外 / 'calendar' = 暦日）。
 * - EF = ES を起点に (tododays - 1) 稼働日進めた日。
 * - フォワードパス → プロジェクト完了日 → バックワードパスで LS/LF/フロート。
 * - フロート 0 = クリティカルパス。依存に循環があれば例外。
 * - 先行が非wbs（todo/課題/リスク/成果物）の場合は、その duedate を FS 固定制約に。
 */
class WbsScheduler
{
    /** @var Collection<int, Wbs> */
    private Collection $leaves;

    /** @var array<int, list<array{id:int,dep:string,lag:int}>> successorId => predecessor links */
    private array $preds = [];

    /** @var array<int, list<array{id:int,dep:string,lag:int}>> predId => successor links */
    private array $succs = [];

    /** @var array<int, Carbon> 非wbs先行の duedate 制約 */
    private array $externalConstraint = [];

    private ?Carbon $projectStart = null;

    public function __construct(
        private readonly Collection $allWbs,
        private readonly WorkCalendar $cal = new WorkCalendar('calendar'),
    ) {}

    public static function forCurrentSite(string $calendarMode = 'working'): self
    {
        return new self(
            Wbs::query()->notDeleted()->get(),
            WorkCalendar::forCurrentSite($calendarMode),
        );
    }

    public function compute(): array
    {
        $this->prepare();
        $order = $this->topoSort();

        $es = [];
        $ef = [];

        foreach ($order as $id) {
            $duration = max(0, (int) $this->leaves[$id]->tododays);
            $candidates = [];

            foreach ($this->preds[$id] ?? [] as $p) {
                if (! isset($es[$p['id']])) {
                    continue;
                }
                $candidates[] = $this->forwardConstraint($es[$p['id']], $ef[$p['id']], $p['dep'], $p['lag'], $duration);
            }

            if (isset($this->externalConstraint[$id])) {
                $candidates[] = $this->cal->addWorkingDays($this->externalConstraint[$id], 1);
            }

            $ownStart = $this->leaves[$id]->godate ?? $this->leaves[$id]->start_date;
            if ($ownStart) {
                $candidates[] = $this->cal->addWorkingDays($ownStart->copy()->startOfDay(), 0);
            }
            if ($candidates === []) {
                $candidates[] = $this->cal->addWorkingDays($this->projectStart, 0);
            }

            $es[$id] = collect($candidates)->sortByDesc(fn (Carbon $c) => $c->timestamp)->first();
            $ef[$id] = $this->cal->addWorkingDays($es[$id], max(0, $duration - 1));
        }

        $projectEnd = collect($ef)->sortByDesc(fn (Carbon $c) => $c->timestamp)->first();

        // バックワードパス
        $lf = [];
        $ls = [];
        foreach (array_reverse($order) as $id) {
            $duration = max(0, (int) $this->leaves[$id]->tododays);
            $candidates = [];

            foreach ($this->succs[$id] ?? [] as $s) {
                if (! isset($ls[$s['id']])) {
                    continue;
                }
                $candidates[] = $this->backwardConstraint($ls[$s['id']], $lf[$s['id']], $s['dep'], $s['lag'], $duration);
            }

            $lf[$id] = $candidates === []
                ? ($projectEnd?->copy() ?? $ef[$id]->copy())
                : collect($candidates)->sortBy(fn (Carbon $c) => $c->timestamp)->first();
            $ls[$id] = $this->cal->addWorkingDays($lf[$id], -max(0, $duration - 1));
        }

        $nodes = [];
        foreach ($order as $id) {
            $float = (isset($es[$id], $ls[$id]))
                ? $this->cal->workingDaysBetween($es[$id], $ls[$id])
                : null;
            $nodes[$id] = [
                'es' => $es[$id] ?? null,
                'ef' => $ef[$id] ?? null,
                'ls' => $ls[$id] ?? null,
                'lf' => $lf[$id] ?? null,
                'float' => $float,
                'critical' => $float !== null && $float <= 0,
                'scheduled' => true,
            ];
        }

        return [
            'nodes' => $nodes,
            'rollup' => $this->rollup($es, $ef),
            'project_start' => $this->projectStart,
            'project_end' => $projectEnd,
        ];
    }

    /** 先行(pEs/pEf) + 依存(dep/lag) から、後続の ES 下限を返す。 */
    private function forwardConstraint(Carbon $pEs, Carbon $pEf, string $dep, int $lag, int $succDuration): Carbon
    {
        $shift = fn (Carbon $anchor, int $extra) => $this->cal->addWorkingDays($anchor, $extra + $lag);

        return match ($dep) {
            'SS' => $shift($pEs, 0),
            'FF' => $this->cal->addWorkingDays($shift($pEf, 0), -max(0, $succDuration - 1)),
            'SF' => $this->cal->addWorkingDays($shift($pEs, 0), -max(0, $succDuration - 1)),
            default => $shift($pEf, 1), // FS
        };
    }

    /** 後続(sLs/sLf) + 依存(dep/lag) から、先行の LF 上限を返す。 */
    private function backwardConstraint(Carbon $sLs, Carbon $sLf, string $dep, int $lag, int $predDuration): Carbon
    {
        return match ($dep) {
            'SS' => $this->cal->addWorkingDays($this->cal->addWorkingDays($sLs, -$lag), max(0, $predDuration - 1)),
            'FF' => $this->cal->addWorkingDays($sLf, -$lag),
            'SF' => $this->cal->addWorkingDays($this->cal->addWorkingDays($sLf, -$lag), max(0, $predDuration - 1)),
            default => $this->cal->addWorkingDays($sLs, -(1 + $lag)), // FS
        };
    }

    private function prepare(): void
    {
        $this->leaves = $this->allWbs->filter(fn (Wbs $w) => ! $w->iscategory)->keyBy('id');
        $leafIds = $this->leaves->keys()->all();

        foreach (Relation::query()->active()->where('rtype', Relation::SEQUENCE)->get() as $r) {
            $fromKind = strtolower((string) $r->id_from_kind);
            $toKind = strtolower((string) $r->id_to_kind);
            $fromId = (int) $r->id_from;
            $toId = (int) $r->id_to;
            $dep = in_array($r->dep_type, ['FS', 'SS', 'FF', 'SF'], true) ? $r->dep_type : 'FS';
            $lag = (int) $r->lag_days;

            if ($toKind !== 'wbs' || ! in_array($toId, $leafIds, true)) {
                continue;
            }

            if ($fromKind === 'wbs' && in_array($fromId, $leafIds, true)) {
                $this->preds[$toId][] = ['id' => $fromId, 'dep' => $dep, 'lag' => $lag];
                $this->succs[$fromId][] = ['id' => $toId, 'dep' => $dep, 'lag' => $lag];
            } else {
                $due = TaskRef::endDate(TaskRef::resolve($fromKind, $fromId));
                if ($due) {
                    $cur = $this->externalConstraint[$toId] ?? null;
                    if (! $cur || $due->gt($cur)) {
                        $this->externalConstraint[$toId] = $due->copy()->startOfDay();
                    }
                }
            }
        }

        $starts = $this->leaves->map(fn (Wbs $w) => $w->godate ?? $w->start_date)->filter();
        $this->projectStart = ($starts->isNotEmpty()
            ? $starts->sortBy(fn (Carbon $c) => $c->timestamp)->first()
            : Carbon::today())->copy()->startOfDay();
    }

    /** @return list<int> */
    private function topoSort(): array
    {
        $ids = $this->leaves->keys()->all();
        $indeg = array_fill_keys($ids, 0);
        foreach ($this->succs as $tos) {
            foreach ($tos as $to) {
                if (isset($indeg[$to['id']])) {
                    $indeg[$to['id']]++;
                }
            }
        }

        $queue = array_values(array_filter($ids, fn ($id) => $indeg[$id] === 0));
        $order = [];
        while ($queue !== []) {
            $id = array_shift($queue);
            $order[] = $id;
            foreach ($this->succs[$id] ?? [] as $to) {
                if (isset($indeg[$to['id']]) && --$indeg[$to['id']] === 0) {
                    $queue[] = $to['id'];
                }
            }
        }

        if (count($order) !== count($ids)) {
            throw new \RuntimeException('先行・後続の依存関係に循環があります。');
        }

        return $order;
    }

    /**
     * @param  array<int, Carbon>  $es
     * @param  array<int, Carbon>  $ef
     * @return array<int, array{start:?Carbon,end:?Carbon}>
     */
    private function rollup(array $es, array $ef): array
    {
        $byFather = $this->allWbs->groupBy(fn (Wbs $w) => (int) $w->father_id);

        $leafIdsUnder = function ($nodeId) use (&$leafIdsUnder, $byFather) {
            $out = [];
            foreach ($byFather[(int) $nodeId] ?? [] as $child) {
                if ($child->iscategory) {
                    $out = array_merge($out, $leafIdsUnder($child->id));
                } else {
                    $out[] = (int) $child->id;
                }
            }

            return $out;
        };

        $result = [];
        foreach ($this->allWbs->filter(fn (Wbs $w) => (bool) $w->iscategory) as $cat) {
            $lids = $leafIdsUnder($cat->id);
            $starts = collect($lids)->map(fn ($l) => $es[$l] ?? null)->filter();
            $ends = collect($lids)->map(fn ($l) => $ef[$l] ?? null)->filter();
            $result[$cat->id] = [
                'start' => $starts->isNotEmpty() ? $starts->sortBy(fn (Carbon $c) => $c->timestamp)->first() : null,
                'end' => $ends->isNotEmpty() ? $ends->sortByDesc(fn (Carbon $c) => $c->timestamp)->first() : null,
            ];
        }

        return $result;
    }
}
