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

        $with = ['statusMaster', 'categoryModel', 'assignee', 'creator'];
        if ($tk->has('team')) {
            $with[] = 'team';
        }

        $task = $tk->query()->notDeleted()->with($with)->findOrFail($id);

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

        $task = $tk->newModel();
        $task->fill($this->validated($request, $tk));
        $task->maker = $request->user()->getKey();
        $task->renewdate = now();
        $task->delete_to = 0;

        if (blank($task->status)) {
            $task->status = StatusMaster::query()
                ->whereRaw('lower(kind) = ?', [strtolower($tk->statusKind())])
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

    public function destroy(string $kind, int $id): RedirectResponse
    {
        $tk = $this->kind($kind);
        $task = $tk->query()->notDeleted()->findOrFail($id);

        $task->delete_to = 1;
        $task->renewdate = now();
        $task->save();

        return redirect()->route('tasks.index', $tk->slug)
            ->with('status', "{$tk->label}を削除しました。");
    }

    private function validated(Request $request, TaskKind $tk): array
    {
        $memberIds = $this->siteMemberIds();

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'status' => ['nullable', Rule::exists('statuses', 'id')],
            'category' => ['nullable', Rule::exists('categories', 'id')->where('kind', $tk->statusKind())],
            'person_do' => ['nullable', Rule::in($memberIds)],
        ];
        $names = [
            'title' => 'タイトル', 'status' => 'ステータス', 'category' => '分類', 'person_do' => '担当者',
        ];

        if ($tk->has('content')) {
            $rules['content'] = ['nullable', 'string'];
            $names['content'] = '内容';
        }
        if ($tk->has('team')) {
            $rules['team_id'] = ['nullable', 'integer'];
            $names['team_id'] = '主管チーム';
        }
        if ($tk->has('situation')) {
            $rules['situation'] = ['nullable', 'string'];
            $names['situation'] = '状況';
        }
        if ($tk->has('criteria')) {
            $rules['completioncriteria'] = ['nullable', 'string'];
            $names['completioncriteria'] = '完了基準';
        }
        if ($tk->has('approver')) {
            $rules['approver'] = ['nullable', Rule::in($memberIds)];
            $names['approver'] = '承認者';
        }
        if ($tk->has('responsible')) {
            $rules['responsible_party'] = ['nullable', 'string', 'max:255'];
            $names['responsible_party'] = '責任者';
        }
        if ($tk->has('stage')) {
            $rules['stage'] = ['nullable', Rule::exists('categories', 'id')->where('kind', 'stage')];
            $names['stage'] = 'ステージ';
        }
        if ($tk->has('date') && $tk->dateColumn()) {
            $rules[$tk->dateColumn()] = ['nullable', 'date'];
            $names[$tk->dateColumn()] = $tk->dateLabel;
        }

        return $request->validate($rules, [], $names);
    }

    private function formOptions(TaskKind $tk): array
    {
        $kindStr = $tk->statusKind();

        return [
            'statuses' => StatusMaster::query()->whereRaw('lower(kind) = ?', [strtolower($kindStr)])->orderBy('junban')->get(),
            'categories' => Category::query()->whereRaw('lower(kind) = ?', [strtolower($kindStr)])->orderBy('junban')->get(),
            'stages' => $tk->has('stage')
                ? Category::query()->where('kind', 'stage')->orderBy('junban')->get()
                : collect(),
            'teams' => Level::query()->where('level', '>=', 0)->orderBy('level')->get(),
            'members' => Member::query()->whereIn('member_id', $this->siteMemberIds())->orderBy('name')->get(['member_id', 'name']),
        ];
    }

    /** @return array<int, string> */
    private function siteMemberIds(): array
    {
        return MemberRoom::query()
            ->where('site_id', app(CurrentSite::class)->id())
            ->pluck('member_id')->all();
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
