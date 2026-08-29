<?php

namespace App\Support;

use App\Models\Relation;
use App\Models\Wbs;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * WBS の簡易スケジューリング（CPM: Critical Path Method / Finish-to-Start）。
 *
 * - 期間は tododays（暦日）。EF = ES + max(0, tododays - 1)。
 * - 後続の ES = すべての先行の EF + 1日、かつ自身の godate（あれば）以上。
 * - 先行が非wbs（todo/課題/リスク/成果物）の場合は、その duedate を固定制約として使う。
 * - フォワードパス → プロジェクト完了日 → バックワードパスで LS/LF/フロートを算出。
 * - フロート 0 = クリティカルパス。
 * - 依存に循環があれば例外。
 */
class WbsScheduler
{
    /** @var Collection<int, Wbs> リーフ（iscategory=false, tododays を持つ）タスク */
    private Collection $leaves;

    /** @var array<int, list<array{kind:string,id:int}>> successorId => [predecessor refs] */
    private array $preds = [];

    /** @var array<int, list<int>> wbsId => [successor wbs ids]（wbs→wbs のみ） */
    private array $succs = [];

    /** @var array<int, Carbon> 非wbs先行の duedate 制約: successorWbsId => 最大duedate */
    private array $externalConstraint = [];

    private ?Carbon $projectStart = null;

    public function __construct(private readonly Collection $allWbs)
    {
    }

    public static function forCurrentSite(): self
    {
        return new self(Wbs::query()->notDeleted()->get());
    }

    /**
     * @return array{
     *   nodes: array<int, array{es:?Carbon,ef:?Carbon,ls:?Carbon,lf:?Carbon,float:?int,critical:bool,scheduled:bool}>,
     *   rollup: array<int, array{start:?Carbon,end:?Carbon}>,
     *   project_start:?Carbon, project_end:?Carbon,
     * }
     */
    public function compute(): array
    {
        $this->prepare();

        $order = $this->topoSort(); // 例外 or ソート済み wbs id
        $es = [];
        $ef = [];

        foreach ($order as $id) {
            $leaf = $this->leaves[$id];
            $duration = max(0, (int) $leaf->tododays);

            $candidates = [];
            // wbs 先行
            foreach ($this->preds[$id] ?? [] as $p) {
                if ($p['kind'] === 'wbs' && isset($ef[$p['id']])) {
                    $candidates[] = $ef[$p['id']]->copy()->addDay();
                }
            }
            // 非wbs 先行（固定制約）
            if (isset($this->externalConstraint[$id])) {
                $candidates[] = $this->externalConstraint[$id]->copy()->addDay();
            }
            // 自身の着手予定
            $ownStart = $leaf->godate ?? $leaf->start_date;
            if ($ownStart) {
                $candidates[] = $ownStart->copy()->startOfDay();
            }
            if ($candidates === []) {
                $candidates[] = $this->projectStart->copy();
            }

            $es[$id] = collect($candidates)->sortByDesc(fn ($c) => $c->timestamp)->first();
            $ef[$id] = $es[$id]->copy()->addDays(max(0, $duration - 1));
        }

        $projectEnd = collect($ef)->sortByDesc(fn ($c) => $c->timestamp)->first();

        // バックワードパス
        $lf = [];
        $ls = [];
        foreach (array_reverse($order) as $id) {
            $duration = max(0, (int) $this->leaves[$id]->tododays);
            $succLs = [];
            foreach ($this->succs[$id] ?? [] as $sid) {
                if (isset($ls[$sid])) {
                    $succLs[] = $ls[$sid]->copy()->subDay();
                }
            }
            $lf[$id] = $succLs === [] ? ($projectEnd?->copy() ?? $ef[$id]->copy())
                : collect($succLs)->sortBy(fn ($c) => $c->timestamp)->first();
            $ls[$id] = $lf[$id]->copy()->subDays(max(0, $duration - 1));
        }

        $nodes = [];
        foreach ($order as $id) {
            $float = ($es[$id] ?? null) && ($ls[$id] ?? null)
                ? (int) round($es[$id]->diffInDays($ls[$id], false))
                : null;
            $nodes[$id] = [
                'es' => $es[$id] ?? null,
                'ef' => $ef[$id] ?? null,
                'ls' => $ls[$id] ?? null,
                'lf' => $lf[$id] ?? null,
                'float' => $float,
                'critical' => $float === 0,
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

    private function prepare(): void
    {
        $this->leaves = $this->allWbs
            ->filter(fn (Wbs $w) => ! $w->iscategory)
            ->keyBy('id');

        $leafIds = $this->leaves->keys()->all();

        $rels = Relation::query()->active()->where('rtype', Relation::SEQUENCE)->get();

        foreach ($rels as $r) {
            $fromKind = strtolower((string) $r->id_from_kind);
            $toKind = strtolower((string) $r->id_to_kind);
            $fromId = (int) $r->id_from;
            $toId = (int) $r->id_to;

            if ($toKind !== 'wbs' || ! in_array($toId, $leafIds, true)) {
                continue; // 後続が wbs リーフでなければスケジュール対象外
            }

            if ($fromKind === 'wbs' && in_array($fromId, $leafIds, true)) {
                $this->preds[$toId][] = ['kind' => 'wbs', 'id' => $fromId];
                $this->succs[$fromId][] = $toId;
            } else {
                // 非wbs 先行: その duedate を固定制約に
                $m = TaskRef::resolve($fromKind, $fromId);
                $due = TaskRef::endDate($m);
                if ($due) {
                    $cur = $this->externalConstraint[$toId] ?? null;
                    if (! $cur || $due->gt($cur)) {
                        $this->externalConstraint[$toId] = $due->copy()->startOfDay();
                    }
                }
            }
        }

        $starts = $this->leaves
            ->map(fn (Wbs $w) => $w->godate ?? $w->start_date)
            ->filter();
        $this->projectStart = ($starts->isNotEmpty()
            ? $starts->sortBy(fn ($c) => $c->timestamp)->first()
            : Carbon::today())->copy()->startOfDay();
    }

    /** @return list<int> */
    private function topoSort(): array
    {
        $ids = $this->leaves->keys()->all();
        $indeg = array_fill_keys($ids, 0);
        foreach ($this->succs as $from => $tos) {
            foreach ($tos as $to) {
                if (isset($indeg[$to])) {
                    $indeg[$to]++;
                }
            }
        }

        $queue = array_values(array_filter($ids, fn ($id) => $indeg[$id] === 0));
        $order = [];
        while ($queue !== []) {
            $id = array_shift($queue);
            $order[] = $id;
            foreach ($this->succs[$id] ?? [] as $to) {
                if (isset($indeg[$to]) && --$indeg[$to] === 0) {
                    $queue[] = $to;
                }
            }
        }

        if (count($order) !== count($ids)) {
            throw new \RuntimeException('先行・後続の依存関係に循環があります。');
        }

        return $order;
    }

    /**
     * サマリ(iscategory)ノードの開始/完了を、配下リーフの計算値からロールアップ。
     *
     * @param  array<int, Carbon>  $es
     * @param  array<int, Carbon>  $ef
     * @return array<int, array{start:?Carbon,end:?Carbon}>
     */
    private function rollup(array $es, array $ef): array
    {
        $byFather = $this->allWbs->groupBy(fn (Wbs $w) => (int) $w->father_id);

        $collectLeafIds = function ($nodeId) use (&$collectLeafIds, $byFather) {
            $out = [];
            foreach ($byFather[(int) $nodeId] ?? [] as $child) {
                if ($child->iscategory) {
                    $out = array_merge($out, $collectLeafIds($child->id));
                } else {
                    $out[] = (int) $child->id;
                }
            }

            return $out;
        };

        $result = [];
        foreach ($this->allWbs->filter(fn (Wbs $w) => (bool) $w->iscategory) as $cat) {
            $leafIds = $collectLeafIds($cat->id);
            $starts = collect($leafIds)->map(fn ($lid) => $es[$lid] ?? null)->filter();
            $ends = collect($leafIds)->map(fn ($lid) => $ef[$lid] ?? null)->filter();
            $result[$cat->id] = [
                'start' => $starts->isNotEmpty() ? $starts->sortBy(fn ($c) => $c->timestamp)->first() : null,
                'end' => $ends->isNotEmpty() ? $ends->sortByDesc(fn ($c) => $c->timestamp)->first() : null,
            ];
        }

        return $result;
    }
}
