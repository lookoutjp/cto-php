<?php

namespace App\Livewire\Member;

use App\Models\Room;
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

    /** @var array<string, string> */
    public array $filters = [
        'my' => '私の担当',
        'mynew' => '新規',
        'mydoing' => '対応中',
        'myhere' => '期限接近',
        'mylate' => '遅延',
        'myfinished' => '完了',
        'all' => 'すべて',
    ];

    private const SORTABLE = ['id', 'title', 'status', 'person_do', 'team_id', 'duedate', 'maker', 'renewdate'];

    public function mount(string $kind): void
    {
        $tk = $this->taskKind(); // 不正な kind なら 404

        if (! Room::find(app(CurrentSite::class)->id())?->hasFunction($tk->function)) {
            throw new NotFoundHttpException;
        }
    }

    public function setView(string $view): void
    {
        $this->view = array_key_exists($view, $this->filters) ? $view : 'my';
        $this->resetPage();
    }

    public function sortBy(string $column): void
    {
        if (! in_array($column, self::SORTABLE, true)) {
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

    private function taskKind(): TaskKind
    {
        return TaskKind::fromSlug($this->kind);
    }

    public function render(): View
    {
        $tk = $this->taskKind();
        $memberId = auth()->id();

        $sortCol = in_array($this->sort, self::SORTABLE, true) ? $this->sort : 'renewdate';
        $sortDir = $this->dir === 'asc' ? 'asc' : 'desc';

        $tasks = $tk->query()
            ->notDeleted()
            ->viewFilter($this->view, $memberId)
            ->when($this->keyword !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('title', 'ilike', '%'.$this->keyword.'%')
                ->orWhere('content', 'ilike', '%'.$this->keyword.'%')))
            ->with(['statusMaster', 'categoryModel', 'assignee', 'creator', 'team'])
            ->orderBy($sortCol, $sortDir)
            ->paginate(20);

        return view('livewire.member.task-list', [
            'tk' => $tk,
            'tasks' => $tasks,
        ])->layout('layouts.app');
    }
}
