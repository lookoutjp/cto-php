<?php

namespace App\Livewire\Member;

use App\Models\Member;
use App\Models\MemberRoom;
use App\Models\Room;
use App\Models\StatusMaster;
use App\Support\CurrentSite;
use App\Support\TaskKind;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TaskList extends Component
{
    use WithPagination;

    public string $kind;

    #[Url(as: 'view')]
    public string $view = 'my';

    #[Url(as: 'q')]
    public string $keyword = '';

    #[Url]
    public string $sort = 'renewdate';

    #[Url]
    public string $dir = 'desc';

    public function mount(string $kind): void
    {
        $tk = TaskKind::fromSlug($this->kind);

        if (! Room::find(app(CurrentSite::class)->id())?->hasFunction($tk->function)) {
            throw new NotFoundHttpException;
        }
    }

    /** @return array<string, string> */
    public function getFiltersProperty(): array
    {
        $tk = TaskKind::fromSlug($this->kind);

        $filters = ['my' => '私の担当', 'mynew' => '新規', 'mydoing' => '対応中'];
        if ($tk->has('date')) {
            $filters['myhere'] = '期限接近';
            $filters['mylate'] = '遅延';
        }
        $filters['myfinished'] = '完了';
        $filters['all'] = 'すべて';

        return $filters;
    }

    /** @return list<string> */
    private function sortable(TaskKind $tk): array
    {
        $cols = ['id', 'title', 'status', 'person_do', 'renewdate'];
        if ($tk->has('team')) {
            $cols[] = 'team_id';
        }
        if ($tk->dateColumn()) {
            $cols[] = $tk->dateColumn();
        }

        return $cols;
    }

    public function setView(string $view): void
    {
        $this->view = array_key_exists($view, $this->filters) ? $view : 'my';
        $this->resetPage();
    }

    public function sortBy(string $column): void
    {
        if (! in_array($column, $this->sortable(TaskKind::fromSlug($this->kind)), true)) {
            return;
        }
        if ($this->sort === $column) {
            $this->dir = $this->dir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort = $column;
            $this->dir = 'asc';
        }
        $this->resetPage();
    }

    public function updatedKeyword(): void
    {
        $this->resetPage();
    }

    /** 一覧から担当者・ステータスをその場で変更する。 */
    public function quickUpdate(int $taskId, string $field, ?string $value): void
    {
        $tk = TaskKind::fromSlug($this->kind);
        $task = $tk->query()->notDeleted()->find($taskId);
        if (! $task) {
            return;
        }

        if ($field === 'status') {
            $ok = StatusMaster::query()
                ->whereRaw('lower(kind) = ?', [strtolower($tk->statusKind())])
                ->where('id', $value)->exists();
            if (! $ok) {
                return;
            }
            $task->status = (int) $value;
        } elseif ($field === 'person_do') {
            $value = $value ?: null;
            if ($value !== null && ! in_array($value, $this->siteMemberIds(), true)) {
                return;
            }
            $task->person_do = $value;
        } else {
            return;
        }

        $task->renewdate = now();
        $task->save();
    }

    /** ✪ 本日のタスクトグル（dotoday を今日 <-> null）。 */
    public function toggleToday(int $taskId): void
    {
        $tk = TaskKind::fromSlug($this->kind);
        if (! $tk->has('today')) {
            return;
        }

        $task = $tk->query()->notDeleted()->find($taskId);
        if (! $task) {
            return;
        }

        $isToday = $task->dotoday !== null && $task->dotoday->isToday();
        $task->dotoday = $isToday ? null : now()->startOfDay();
        $task->renewdate = now();
        $task->save();
    }

    /** @return array<int, string> */
    private function siteMemberIds(): array
    {
        return MemberRoom::query()
            ->where('site_id', app(CurrentSite::class)->id())
            ->pluck('member_id')->all();
    }

    public function render(): View
    {
        $tk = TaskKind::fromSlug($this->kind);
        $memberId = auth()->id();

        $sortable = $this->sortable($tk);
        $sortCol = in_array($this->sort, $sortable, true) ? $this->sort : 'renewdate';
        $sortDir = $this->dir === 'asc' ? 'asc' : 'desc';

        $with = ['statusMaster', 'categoryModel', 'assignee', 'creator'];
        if ($tk->has('team')) {
            $with[] = 'team';
        }

        $tasks = $tk->query()
            ->notDeleted()
            ->viewFilter($this->view, $memberId)
            ->when($this->keyword !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('title', 'ilike', '%'.$this->keyword.'%')
                ->orWhere('content', 'ilike', '%'.$this->keyword.'%')))
            ->with($with)
            ->orderBy($sortCol, $sortDir)
            ->paginate(20);

        return view('livewire.member.task-list', [
            'tk' => $tk,
            'tasks' => $tasks,
            'statusOptions' => StatusMaster::query()
                ->whereRaw('lower(kind) = ?', [strtolower($tk->statusKind())])
                ->orderBy('junban')->get(['id', 'statusname', 'percent']),
            'memberOptions' => Member::query()
                ->whereIn('member_id', $this->siteMemberIds())
                ->orderBy('name')->get(['member_id', 'name']),
        ])->layout('layouts.app');
    }
}
