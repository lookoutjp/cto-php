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

            <form method="post"
                  action="{{ $mode === 'create' ? route('tasks.store', $tk->slug) : route('tasks.update', [$tk->slug, $task->id]) }}"
                  class="space-y-5 rounded-lg bg-white p-6 shadow-sm">
                @csrf
                @if ($mode === 'edit') @method('PUT') @endif

                @php($val = fn ($f, $d = '') => old($f, $task->{$f} ?? $d))

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
                                <option value="{{ $s->id }}" @selected((string) $val('status') === (string) $s->id)>{{ $s->statusname }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">分類</label>
                        <select name="category" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                            <option value="">—</option>
                            @foreach ($categories as $c)
                                <option value="{{ $c->id }}" @selected((string) $val('category') === (string) $c->id)>{{ $c->categoryname }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">担当者</label>
                        <select name="person_do" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                            <option value="">未設定</option>
                            @foreach ($members as $m)
                                <option value="{{ $m->member_id }}" @selected((string) $val('person_do') === (string) $m->member_id)>{{ $m->name ?: $m->member_id }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">主管チーム</label>
                        <select name="team_id" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                            <option value="">未設定</option>
                            @foreach ($teams as $t)
                                <option value="{{ $t->level }}" @selected((string) $val('team_id') === (string) $t->level)>{{ $t->levelname }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">期限</label>
                        <input type="date" name="duedate"
                               value="{{ old('duedate', optional($task->duedate)->format('Y-m-d')) }}"
                               class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">承認者</label>
                        <select name="approver" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                            <option value="">未設定</option>
                            @foreach ($members as $m)
                                <option value="{{ $m->member_id }}" @selected((string) $val('approver') === (string) $m->member_id)>{{ $m->name ?: $m->member_id }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">内容</label>
                    <textarea name="content" rows="5" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">{{ $val('content') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">状況</label>
                    <textarea name="situation" rows="4" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">{{ $val('situation') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">完了基準</label>
                    <textarea name="completioncriteria" rows="3" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">{{ $val('completioncriteria') }}</textarea>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-gray-700">
                        {{ $mode === 'create' ? '起票する' : '更新する' }}
                    </button>
                    <a href="{{ $mode === 'edit' ? route('tasks.show', [$tk->slug, $task->id]) : route('tasks.index', $tk->slug) }}"
                       class="text-sm text-gray-500 hover:underline">キャンセル</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
