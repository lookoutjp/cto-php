<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            <a href="{{ route('tasks.index', $tk->slug) }}" class="text-gray-500 hover:underline">{{ $tk->label }}一覧</a>
            <span class="text-gray-400">/</span> {{ $mode === 'create' ? '新規起票' : '編集 #'.$task->id }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <ul class="list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            @php($val = fn ($f, $d = '') => old($f, $task->{$f} ?? $d))
            @php($sel = fn ($f, $v) => (string) old($f, $task->{$f} ?? '') === (string) $v)

            <form method="post"
                  action="{{ $mode === 'create' ? route('tasks.store', $tk->slug) : route('tasks.update', [$tk->slug, $task->id]) }}"
                  class="space-y-5 rounded-lg bg-white p-6 shadow-sm">
                @csrf
                @if ($mode === 'edit') @method('PUT') @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700">タイトル <span class="text-red-600">*</span></label>
                    <input type="text" name="title" value="{{ $val('title') }}" required maxlength="255"
                           class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">ステータス</label>
                        <select name="status" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                            <option value="">—</option>
                            @foreach ($statuses as $s)
                                <option value="{{ $s->id }}" @selected($sel('status', $s->id))>{{ $s->statusname }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">分類</label>
                        <select name="category" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                            <option value="">—</option>
                            @foreach ($categories as $c)
                                <option value="{{ $c->id }}" @selected($sel('category', $c->id))>{{ $c->categoryname }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">担当者</label>
                        <select name="person_do" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                            <option value="">未設定</option>
                            @foreach ($members as $m)
                                <option value="{{ $m->member_id }}" @selected($sel('person_do', $m->member_id))>{{ $m->name ?: $m->member_id }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if ($tk->has('team'))
                        <div>
                            <label class="block text-sm font-medium text-gray-700">主管チーム</label>
                            <select name="team_id" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                                <option value="">未設定</option>
                                @foreach ($teams as $t)
                                    <option value="{{ $t->level }}" @selected($sel('team_id', $t->level))>{{ $t->levelname }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    @if ($tk->has('date') && $tk->dateColumn())
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ $tk->dateLabel }}</label>
                            <input type="date" name="{{ $tk->dateColumn() }}"
                                   value="{{ old($tk->dateColumn(), optional($task->{$tk->dateColumn()})->format('Y-m-d')) }}"
                                   class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                        </div>
                    @endif

                    @if ($tk->has('approver'))
                        <div>
                            <label class="block text-sm font-medium text-gray-700">承認者</label>
                            <select name="approver" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                                <option value="">未設定</option>
                                @foreach ($members as $m)
                                    <option value="{{ $m->member_id }}" @selected($sel('approver', $m->member_id))>{{ $m->name ?: $m->member_id }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    @if ($tk->has('stage'))
                        <div>
                            <label class="block text-sm font-medium text-gray-700">ステージ</label>
                            <select name="stage" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                                <option value="">—</option>
                                @foreach ($stages as $s)
                                    <option value="{{ $s->id }}" @selected($sel('stage', $s->id))>{{ $s->categoryname }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    @if ($tk->has('responsible'))
                        <div>
                            <label class="block text-sm font-medium text-gray-700">責任者</label>
                            <input type="text" name="responsible_party" value="{{ $val('responsible_party') }}" maxlength="255"
                                   class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                        </div>
                    @endif
                </div>

                @if ($tk->has('content'))
                    <div>
                        <label class="block text-sm font-medium text-gray-700">内容</label>
                        <textarea name="content" rows="5" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">{{ $val('content') }}</textarea>
                    </div>
                @endif
                @if ($tk->has('situation'))
                    <div>
                        <label class="block text-sm font-medium text-gray-700">状況</label>
                        <textarea name="situation" rows="4" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">{{ $val('situation') }}</textarea>
                    </div>
                @endif
                @if ($tk->has('criteria'))
                    <div>
                        <label class="block text-sm font-medium text-gray-700">完了基準</label>
                        <textarea name="completioncriteria" rows="3" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">{{ $val('completioncriteria') }}</textarea>
                    </div>
                @endif

                @if ($tk->has('changedetail'))
                    <div class="space-y-4 rounded-lg border border-gray-200 p-4">
                        <h3 class="text-sm font-semibold text-gray-500">変更管理</h3>
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">発生日</label>
                                <input type="date" name="occurrence_day"
                                       value="{{ old('occurrence_day', optional($task->occurrence_day)->format('Y-m-d')) }}"
                                       class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">工数見積（時間）</label>
                                <input type="number" name="hour_estimation" step="0.5" min="0" value="{{ $val('hour_estimation') }}"
                                       class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">判定結果</label>
                                <select name="judge_result" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                                    <option value="">未判定</option>
                                    <option value="ok" @selected($sel('judge_result', 'ok'))>承認</option>
                                    <option value="no" @selected($sel('judge_result', 'no'))>却下</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">完了日</label>
                                <input type="date" name="done_day"
                                       value="{{ old('done_day', optional($task->done_day)->format('Y-m-d')) }}"
                                       class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">影響範囲</label>
                            <textarea name="scope_of_impact" rows="3" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">{{ $val('scope_of_impact') }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">対応内容</label>
                            <textarea name="do_content" rows="3" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">{{ $val('do_content') }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">却下理由</label>
                            <textarea name="ng_reason" rows="2" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">{{ $val('ng_reason') }}</textarea>
                        </div>
                    </div>
                @endif

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="rounded-lg bg-brand px-5 py-2.5 text-sm font-medium text-brand-fg hover:bg-brand-dark">
                        {{ $mode === 'create' ? '起票する' : '更新する' }}
                    </button>
                    <a href="{{ $mode === 'edit' ? route('tasks.show', [$tk->slug, $task->id]) : route('tasks.index', $tk->slug) }}"
                       class="text-sm text-gray-500 hover:underline">キャンセル</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
