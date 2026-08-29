<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            <a href="{{ route('wbs.index') }}" class="text-gray-500 hover:underline">WBS</a>
            <span class="text-gray-400">/</span>
            {{ $mode === 'create' ? ($parent ? "「{$parent->title}」に子項目を追加" : 'ルート項目を追加') : '編集 #'.$node->id }}
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

            @php($val = fn ($f, $d = '') => old($f, $node->{$f} ?? $d))
            @php($d = fn ($f) => old($f, optional($node->{$f})->format('Y-m-d')))

            <form method="post"
                  action="{{ $mode === 'create' ? route('wbs.store') : route('wbs.update', $node->id) }}"
                  class="space-y-5 rounded-lg bg-white p-6 shadow-sm">
                @csrf
                @if ($mode === 'edit') @method('PUT') @endif
                @if ($mode === 'create')
                    <input type="hidden" name="father_id" value="{{ $parent->id ?? 0 }}">
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700">タイトル <span class="text-red-600">*</span></label>
                    <input type="text" name="title" value="{{ $val('title') }}" required maxlength="255"
                           class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                </div>

                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="hidden" name="iscategory" value="0">
                    <input type="checkbox" name="iscategory" value="1" @checked((bool) old('iscategory', $node->iscategory))
                           class="rounded border-gray-300 text-gray-900 focus:ring-gray-500">
                    サマリ項目（作業をまとめるカテゴリ）
                </label>

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
                        <label class="block text-sm font-medium text-gray-700">所要日数</label>
                        <input type="number" name="tododays" min="0" value="{{ $val('tododays') }}"
                               class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">着手予定</label>
                        <input type="date" name="godate" value="{{ $d('godate') }}"
                               class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">期限</label>
                        <input type="date" name="duedate" value="{{ $d('duedate') }}"
                               class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">開始予定</label>
                        <input type="date" name="start_date" value="{{ $d('start_date') }}"
                               class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">完了予定</label>
                        <input type="date" name="complete_date" value="{{ $d('complete_date') }}"
                               class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">
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

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="rounded-lg bg-brand px-5 py-2.5 text-sm font-medium text-brand-fg hover:bg-brand-dark">
                        {{ $mode === 'create' ? '追加する' : '更新する' }}
                    </button>
                    <a href="{{ $mode === 'edit' ? route('wbs.show', $node->id) : route('wbs.index') }}"
                       class="text-sm text-gray-500 hover:underline">キャンセル</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
