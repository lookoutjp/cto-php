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
