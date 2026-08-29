<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Level;
use App\Models\Member;
use App\Models\MemberRoom;
use App\Models\Room;
use App\Models\StatusMaster;
use App\Support\CurrentSite;
use App\Support\TaskKind;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TaskController extends Controller
{
    public function show(string $kind, int $id): View
    {
        $tk = $this->kind($kind);

        $task = $tk->query()->notDeleted()
            ->with(['statusMaster', 'categoryModel', 'assignee', 'creator', 'team'])
            ->findOrFail($id);

        return view('member.task-show', ['tk' => $tk, 'task' => $task]);
    }

    public function create(string $kind): View
    {
        $tk = $this->kind($kind);

        return view('member.task-form', [
            'tk' => $tk,
            'task' => $tk->newModel(),
            'mode' => 'create',
        ] + $this->formOptions($tk));
    }

    public function edit(string $kind, int $id): View
    {
        $tk = $this->kind($kind);
        $task = $tk->query()->notDeleted()->findOrFail($id);

        return view('member.task-form', [
            'tk' => $tk,
            'task' => $task,
            'mode' => 'edit',
        ] + $this->formOptions($tk));
    }

    public function store(string $kind, Request $request): RedirectResponse
    {
        $tk = $this->kind($kind);
        $data = $this->validated($request, $tk);

        $task = $tk->newModel();
        $task->fill($data);
        $task->maker = $request->user()->getKey();
        $task->renewdate = now();
        $task->delete_to = 0;

        // problems / risks の status は NOT NULL。未指定なら junban 先頭を既定に。
        if (blank($task->status)) {
            $task->status = StatusMaster::query()
                ->whereRaw('lower(kind) = ?', [strtolower($tk->model::$taskKind)])
                ->orderBy('junban')->value('id');
        }

        $task->save(); // BelongsToSite が site_id を自動セット

        return redirect()->route('tasks.show', [$tk->slug, $task->id])
            ->with('status', "{$tk->label}を起票しました。");
    }

    public function update(string $kind, int $id, Request $request): RedirectResponse
    {
        $tk = $this->kind($kind);
        $task = $tk->query()->notDeleted()->findOrFail($id);

        $task->fill($this->validated($request, $tk));
        $task->renewdate = now();
        $task->save();

        return redirect()->route('tasks.show', [$tk->slug, $task->id])
            ->with('status', "{$tk->label}を更新しました。");
    }

    public function destroy(string $kind, int $id, Request $request): RedirectResponse
    {
        $tk = $this->kind($kind);
        $task = $tk->query()->notDeleted()->findOrFail($id);

        // 論理削除（旧ASP delete_to=1）
        $task->delete_to = 1;
        $task->renewdate = now();
        $task->save();

        return redirect()->route('tasks.index', $tk->slug)
            ->with('status', "{$tk->label}を削除しました。");
    }

    private function validated(Request $request, TaskKind $tk): array
    {
        $siteMemberIds = $this->siteMemberIds();
        $kindStr = $tk->model::$taskKind;

        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'situation' => ['nullable', 'string'],
            'category' => ['nullable', Rule::exists('categories', 'id')->where('kind', $kindStr)],
            'status' => ['nullable', Rule::exists('statuses', 'id')],
            'person_do' => ['nullable', Rule::in($siteMemberIds)],
            'team_id' => ['nullable', 'integer'],
            'duedate' => ['nullable', 'date'],
            'approver' => ['nullable', Rule::in($siteMemberIds)],
            'completioncriteria' => ['nullable', 'string'],
        ], [], [
            'title' => 'タイトル',
            'content' => '内容',
            'situation' => '状況',
            'category' => '分類',
            'status' => 'ステータス',
            'person_do' => '担当者',
            'team_id' => '主管チーム',
            'duedate' => '期限',
            'approver' => '承認者',
            'completioncriteria' => '完了基準',
        ]);
    }

    private function formOptions(TaskKind $tk): array
    {
        $kindStr = $tk->model::$taskKind;

        return [
            'statuses' => StatusMaster::query()->whereRaw('lower(kind) = ?', [strtolower($kindStr)])->orderBy('junban')->get(),
            'categories' => Category::query()->whereRaw('lower(kind) = ?', [strtolower($kindStr)])->orderBy('junban')->get(),
            'teams' => Level::query()->where('level', '>=', 0)->orderBy('level')->get(),
            'members' => Member::query()->whereIn('member_id', $this->siteMemberIds())->orderBy('name')->get(['member_id', 'name']),
        ];
    }

    /** @return array<int, string> */
    private function siteMemberIds(): array
    {
        $site = app(CurrentSite::class)->id();

        return MemberRoom::query()->where('site_id', $site)->pluck('member_id')->all();
    }

    private function kind(string $slug): TaskKind
    {
        $tk = TaskKind::fromSlug($slug);

        if (! Room::find(app(CurrentSite::class)->id())?->hasFunction($tk->function)) {
            throw new NotFoundHttpException;
        }

        return $tk;
    }
}
