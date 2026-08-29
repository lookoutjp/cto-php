<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Level;
use App\Models\Member;
use App\Models\MemberRoom;
use App\Models\Room;
use App\Models\StatusMaster;
use App\Models\Wbs;
use App\Support\CurrentSite;
use App\Support\WbsScheduler;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * 会員向け WBS（階層）。旧 wbs.asp / WbsAdd.asp / WbsDetail.asp 相当。
 * 閲覧・追加・編集・論理削除・並び替え（上下 / インデント / アウトデント）。
 */
class WbsController extends Controller
{
    public function index(): View
    {
        $this->ensureEnabled();

        return view('member.wbs-index', ['roots' => Wbs::tree()]);
    }

    public function show(int $id): View
    {
        $this->ensureEnabled();

        $node = $this->find($id)->load(['statusMaster', 'assignee', 'creator', 'team', 'parent']);

        return view('member.wbs-show', [
            'node' => $node,
            'children' => $node->children()->notDeleted()->with(['statusMaster', 'assignee'])->get(),
        ]);
    }

    /**
     * 計画期間・工数チェック（旧 WBS_CheckFromTo.asp / WBS_CheckDays.asp）。
     * サマリ(iscategory)ノードの計画値と、配下タスクの実集計を並べて差異を見せる。
     */
    public function check(): View
    {
        $this->ensureEnabled();

        $all = Wbs::query()->notDeleted()->get();
        $byFather = $all->groupBy(fn ($w) => (int) $w->father_id);

        // 各ノードの子孫（リーフ含む全部）を集める
        $descendants = function ($id) use (&$descendants, $byFather) {
            $out = collect();
            foreach ($byFather[(int) $id] ?? [] as $child) {
                $out->push($child);
                $out = $out->merge($descendants($child->id));
            }

            return $out;
        };

        $rows = $all->filter(fn ($w) => (bool) $w->iscategory)
            ->sortBy([['deep', 'asc'], ['junban', 'asc']])
            ->map(function ($cat) use ($descendants) {
                $desc = $descendants($cat->id);
                $leaves = $desc->filter(fn ($d) => ! $d->iscategory);

                $sumDays = $leaves->sum(fn ($d) => (int) $d->tododays);
                $starts = $leaves->map(fn ($d) => $d->godate ?? $d->start_date)->filter();
                $ends = $leaves->map(fn ($d) => $d->duedate ?? $d->complete_date)->filter();
                $minStart = $starts->sortBy(fn ($c) => $c->timestamp)->first();
                $maxEnd = $ends->sortByDesc(fn ($c) => $c->timestamp)->first();

                return (object) [
                    'node' => $cat,
                    'has_tasks' => $leaves->isNotEmpty(),
                    'plan_days' => $cat->tododays,
                    'actual_days' => $sumDays,
                    'plan_start' => $cat->godate,
                    'actual_start' => $minStart,
                    'plan_end' => $cat->duedate,
                    'actual_end' => $maxEnd,
                ];
            })->values();

        return view('member.wbs-check', ['rows' => $rows]);
    }

    /**
     * スケジュール計算（CPM）のプレビュー。旧ASP には無い新機能。
     * 先行→後続の Finish-to-Start で各リーフの最早開始/完了を計算し、
     * 現在の日付と並べて表示。クリティカルパスも算出。
     */
    public function schedule(Request $request): View
    {
        $this->ensureEnabled();

        $all = Wbs::query()->notDeleted()->get();
        $rootId = $request->integer('root') ?: null;
        $scopeIds = $rootId ? $this->subtreeIds($all, $rootId) : $all->pluck('id')->all();

        try {
            $result = (new WbsScheduler($all))->compute();
            $error = null;
        } catch (\Throwable $e) {
            $result = null;
            $error = $e->getMessage();
        }

        $display = $all->whereIn('id', $scopeIds)
            ->sortBy([['deep', 'asc'], ['junban', 'asc']])->values();

        return view('member.wbs-schedule', [
            'all' => $display,
            'result' => $result,
            'error' => $error,
            'rootId' => $rootId,
            'rootTitle' => $rootId ? optional($all->firstWhere('id', $rootId))->title : null,
        ]);
    }

    /**
     * 計算結果を wbs.godate / wbs.duedate に書き戻す。
     */
    public function applySchedule(Request $request): RedirectResponse
    {
        $this->ensureEnabled();

        $all = Wbs::query()->notDeleted()->get();
        $rootId = $request->integer('root') ?: null;
        $scopeIds = $rootId ? $this->subtreeIds($all, $rootId) : $all->pluck('id')->all();

        try {
            $result = (new WbsScheduler($all))->compute();
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        $updateSummaries = $request->boolean('update_summaries');
        $count = 0;

        DB::transaction(function () use ($result, $updateSummaries, $scopeIds, &$count) {
            foreach ($result['nodes'] as $id => $n) {
                if ($n['es'] === null || ! in_array($id, $scopeIds, true)) {
                    continue;
                }
                Wbs::query()->whereKey($id)->update([
                    'godate' => $n['es']->toDateString(),
                    'duedate' => $n['ef']->toDateString(),
                    'renewdate' => now(),
                ]);
                $count++;
            }

            if ($updateSummaries) {
                foreach ($result['rollup'] as $id => $r) {
                    if ($r['start'] === null || ! in_array($id, $scopeIds, true)) {
                        continue;
                    }
                    Wbs::query()->whereKey($id)->update([
                        'godate' => $r['start']->toDateString(),
                        'duedate' => $r['end']->toDateString(),
                        'renewdate' => now(),
                    ]);
                    $count++;
                }
            }
        });

        return redirect()->route('wbs.schedule', $rootId ? ['root' => $rootId] : [])
            ->with('status', "{$count} 件の日付を更新しました。");
    }

    /** @return list<int> $rootId とその全子孫の id（自身を含む） */
    private function subtreeIds(\Illuminate\Support\Collection $all, int $rootId): array
    {
        $byFather = $all->groupBy(fn ($w) => (int) $w->father_id);
        $out = [$rootId];
        $walk = function ($id) use (&$walk, $byFather, &$out) {
            foreach ($byFather[(int) $id] ?? [] as $child) {
                $out[] = (int) $child->id;
                $walk($child->id);
            }
        };
        $walk($rootId);

        return $out;
    }

    public function create(Request $request): View
    {
        $this->ensureEnabled();

        $parent = $request->filled('parent') ? $this->find((int) $request->integer('parent')) : null;

        return view('member.wbs-form', [
            'node' => new Wbs(['iscategory' => $parent === null]),
            'parent' => $parent,
            'mode' => 'create',
        ] + $this->formOptions());
    }

    public function edit(int $id): View
    {
        $this->ensureEnabled();
        $node = $this->find($id);

        return view('member.wbs-form', [
            'node' => $node,
            'parent' => $node->father_id ? $this->find((int) $node->father_id) : null,
            'mode' => 'edit',
        ] + $this->formOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureEnabled();
        $data = $this->validated($request);

        $fatherId = (int) $request->integer('father_id');
        $parent = $fatherId ? $this->find($fatherId) : null;

        $node = new Wbs($data);
        $node->father_id = $fatherId;
        $node->deep = ($parent->deep ?? 0) + 1;
        $node->junban = (int) Wbs::query()->notDeleted()->where('father_id', $fatherId)->max('junban') + 1;
        $node->maker = $request->user()->getKey();
        $node->renewdate = now();
        $node->delete_to = 0;
        if (blank($node->status)) {
            $node->status = $this->defaultStatusId();
        }
        $node->save();

        return redirect()->route('wbs.show', $node->id)->with('status', 'WBS 項目を追加しました。');
    }

    public function update(int $id, Request $request): RedirectResponse
    {
        $this->ensureEnabled();
        $node = $this->find($id);

        $node->fill($this->validated($request));
        $node->renewdate = now();
        $node->save();

        return redirect()->route('wbs.show', $node->id)->with('status', 'WBS 項目を更新しました。');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->ensureEnabled();
        $node = $this->find($id);

        if ($node->children()->notDeleted()->exists()) {
            return back()->with('error', '子項目があるため削除できません。先に子項目を削除してください。');
        }

        $node->delete_to = 1;
        $node->renewdate = now();
        $node->save();

        return redirect()->route('wbs.index')->with('status', 'WBS 項目を削除しました。');
    }

    /**
     * ドラッグ&ドロップ後のツリー全体を受け取り、father_id / junban / deep を更新する。
     * リクエスト: { nodes: [{ id, parent_id, junban }, ...] }
     */
    public function reorder(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->ensureEnabled();

        $data = $request->validate([
            'nodes' => ['required', 'array'],
            'nodes.*.id' => ['required', 'integer'],
            'nodes.*.parent_id' => ['required', 'integer'],
            'nodes.*.junban' => ['required', 'integer'],
        ]);

        $siteNodeIds = Wbs::query()->notDeleted()->pluck('id')->all();
        $incoming = collect($data['nodes'])
            ->filter(fn ($n) => in_array($n['id'], $siteNodeIds, true))
            ->keyBy('id');

        // 循環（自分の子孫を親にする）を検出したら拒否
        foreach ($incoming as $node) {
            $cursor = $node['parent_id'];
            $guard = 0;
            while ($cursor !== 0 && $guard++ < 1000) {
                if ($cursor === $node['id']) {
                    return response()->json(['message' => '循環する階層は設定できません。'], 422);
                }
                $cursor = $incoming[$cursor]['parent_id'] ?? 0;
            }
        }

        DB::transaction(function () use ($incoming) {
            foreach ($incoming as $n) {
                Wbs::query()->whereKey($n['id'])->update([
                    'father_id' => $n['parent_id'],
                    'junban' => $n['junban'],
                ]);
            }
            // deep をルートから振り直す
            $this->reindexTreeDeep();
        });

        return response()->json(['ok' => true]);
    }

    private function reindexTreeDeep(): void
    {
        $all = Wbs::query()->notDeleted()->get(['id', 'father_id']);
        $byFather = $all->groupBy(fn ($w) => (int) $w->father_id);

        $walk = function ($fatherId, $deep) use (&$walk, $byFather) {
            foreach ($byFather[$fatherId] ?? [] as $node) {
                Wbs::query()->whereKey($node->id)->update(['deep' => $deep]);
                $walk((int) $node->id, $deep + 1);
            }
        };
        $walk(0, 1);
        // father_id が null のルートも
        foreach ($all->whereNull('father_id') as $node) {
            Wbs::query()->whereKey($node->id)->update(['deep' => 1]);
            $walk((int) $node->id, 2);
        }
    }

    // --- helpers ------------------------------------------------------

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'situation' => ['nullable', 'string'],
            'iscategory' => ['nullable', 'boolean'],
            'status' => ['nullable', Rule::exists('statuses', 'id')],
            'person_do' => ['nullable', Rule::in($this->siteMemberIds())],
            'team_id' => ['nullable', 'integer'],
            'godate' => ['nullable', 'date'],
            'duedate' => ['nullable', 'date'],
            'start_date' => ['nullable', 'date'],
            'complete_date' => ['nullable', 'date'],
            'tododays' => ['nullable', 'integer', 'min:0'],
        ], [], [
            'title' => 'タイトル', 'content' => '内容', 'situation' => '状況',
            'status' => 'ステータス', 'person_do' => '担当者', 'team_id' => '主管チーム',
            'godate' => '着手予定', 'duedate' => '期限', 'start_date' => '開始予定',
            'complete_date' => '完了予定', 'tododays' => '所要日数',
        ]);
    }

    private function formOptions(): array
    {
        return [
            'statuses' => StatusMaster::query()->whereRaw("lower(kind) = 'wbs'")->orderBy('junban')->get(),
            'teams' => Level::query()->where('level', '>=', 0)->orderBy('level')->get(),
            'members' => Member::query()->whereIn('member_id', $this->siteMemberIds())->orderBy('name')->get(['member_id', 'name']),
        ];
    }

    private function defaultStatusId(): ?int
    {
        return StatusMaster::query()->whereRaw("lower(kind) = 'wbs'")->orderBy('junban')->value('id');
    }

    /** @return array<int, string> */
    private function siteMemberIds(): array
    {
        return MemberRoom::query()->where('site_id', app(CurrentSite::class)->id())->pluck('member_id')->all();
    }

    private function find(int $id): Wbs
    {
        return Wbs::query()->notDeleted()->findOrFail($id);
    }

    private function ensureEnabled(): void
    {
        if (! Room::find(app(CurrentSite::class)->id())?->hasFunction('wbsfunction')) {
            throw new NotFoundHttpException;
        }
    }
}
