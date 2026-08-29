<?php

namespace App\Livewire\Member;

use App\Models\Member;
use App\Models\Relation;
use App\Support\Relations;
use App\Support\TaskRef;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * WBS / タスク詳細に埋め込む「先行・後続・関連」パネル。
 *   <livewire:member.relations-panel kind="wbs" :id="$node->id" />
 */
class RelationsPanel extends Component
{
    public string $kind;
    public int $id;

    // 追加フォーム
    public string $linkType = 'pred';      // pred | succ | rel
    public string $targetKind = 'wbs';
    public ?int $targetId = null;

    public function mount(string $kind, int $id): void
    {
        abort_unless(auth()->user()?->isProjectMemberOf(), 403);
        abort_unless(array_key_exists($kind, TaskRef::KINDS), 404);
    }

    public function addLink(): void
    {
        abort_unless(auth()->user() instanceof Member && auth()->user()->isProjectMemberOf(), 403);

        $this->validate([
            'targetKind' => ['required', 'in:'.implode(',', array_keys(TaskRef::KINDS))],
            'targetId' => ['required', 'integer'],
            'linkType' => ['required', 'in:pred,succ,rel'],
        ], [
            'targetId.required' => '追加する対象を選択してください。',
            'targetId.integer' => '追加する対象を選択してください。',
        ]);

        if (! TaskRef::resolve($this->targetKind, $this->targetId)) {
            $this->addError('targetId', '対象が見つかりません。');

            return;
        }

        match ($this->linkType) {
            // pred: 相手が先行 → 相手(from) → 自分(to)
            'pred' => Relations::add($this->targetKind, $this->targetId, $this->kind, $this->id, Relation::SEQUENCE),
            // succ: 自分が先行 → 自分(from) → 相手(to)
            'succ' => Relations::add($this->kind, $this->id, $this->targetKind, $this->targetId, Relation::SEQUENCE),
            'rel' => Relations::add($this->kind, $this->id, $this->targetKind, $this->targetId, Relation::RELATED),
        };

        $this->reset('targetId');
    }

    public function removeLink(int $relationId): void
    {
        abort_unless(auth()->user() instanceof Member && auth()->user()->isProjectMemberOf(), 403);
        Relations::remove($relationId);
    }

    public function render(): View
    {
        $predecessors = Relations::predecessors($this->kind, $this->id);
        $selfModel = TaskRef::resolve($this->kind, $this->id);
        $selfStart = TaskRef::startDate($selfModel);

        // 先行タスクの終了 > 自分の開始予定 → 日程の矛盾
        $conflicts = $predecessors->filter(function ($p) use ($selfStart) {
            $pEnd = TaskRef::endDate($p->model);

            return $selfStart && $pEnd && $pEnd->gt($selfStart);
        });

        return view('livewire.member.relations-panel', [
            'predecessors' => $predecessors,
            'successors' => Relations::successors($this->kind, $this->id),
            'related' => Relations::related($this->kind, $this->id),
            'conflicts' => $conflicts,
            'targetOptions' => TaskRef::options($this->targetKind),
            'kinds' => TaskRef::LABELS,
        ]);
    }
}
