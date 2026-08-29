<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            <a href="{{ route('tasks.index', $tk->slug) }}" class="text-gray-500 hover:underline">{{ $tk->label }}一覧</a>
            <span class="text-gray-400">/</span> #{{ $task->id }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('status') }}</div>
            @endif

            <div class="rounded-lg bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <h1 class="text-lg font-bold text-gray-900">{{ $task->title }}</h1>
                    <div class="flex gap-2">
                        <a href="{{ route('tasks.edit', [$tk->slug, $task->id]) }}"
                           class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50">編集</a>
                        <form method="post" action="{{ route('tasks.destroy', [$tk->slug, $task->id]) }}" onsubmit="return confirm('削除しますか？')">
                            @csrf @method('DELETE')
                            <button class="rounded-lg border border-red-300 px-3 py-1.5 text-sm text-red-700 hover:bg-red-50">削除</button>
                        </form>
                    </div>
                </div>

                @php
                    $rows = [
                        ['ステータス', optional($task->statusMaster)->statusname ?? '—'],
                        ['分類', optional($task->categoryModel)->categoryname ?? '—'],
                        ['担当者', optional($task->assignee)->name ?? '未設定'],
                    ];
                    if ($tk->has('team')) $rows[] = ['主管チーム', optional($task->team)->levelname ?? '未設定'];
                    if ($tk->has('responsible')) $rows[] = ['責任者', $task->responsible_party ?: '—'];
                    if ($tk->has('approver')) $rows[] = ['承認者', optional(\App\Models\Member::find($task->approver))->name ?? ($task->approver ?: '—')];
                    $rows[] = ['起票者', optional($task->creator)->name ?? $task->maker ?? '—'];
                    $rows[] = ['更新日', optional($task->renewdate)->isoFormat('YYYY年M月D日') ?? '—'];
                @endphp

                <dl class="mt-5 grid grid-cols-1 gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
                    @if ($tk->has('date'))
                        <div class="flex gap-2">
                            <dt class="w-24 shrink-0 text-gray-500">{{ $tk->dateLabel }}</dt>
                            <dd class="{{ $task->isOverdue() ? 'font-semibold text-red-600' : 'text-gray-900' }}">
                                {{ optional($task->{$tk->dateColumn()})->isoFormat('YYYY年M月D日') ?? '未設定' }}
                            </dd>
                        </div>
                    @endif
                    @foreach ($rows as [$label, $value])
                        <div class="flex gap-2">
                            <dt class="w-24 shrink-0 text-gray-500">{{ $label }}</dt>
                            <dd class="text-gray-900">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>

            @php
                $panels = [];
                if ($tk->has('content')) $panels['内容'] = $task->content;
                if ($tk->has('situation')) $panels['状況'] = $task->situation;
                if ($tk->has('criteria')) $panels['完了基準'] = $task->completioncriteria;
            @endphp
            @foreach ($panels as $label => $body)
                @if (filled($body))
                    <div class="rounded-lg bg-white p-6 shadow-sm">
                        <h3 class="mb-2 text-sm font-semibold text-gray-500">{{ $label }}</h3>
                        <div class="prose prose-sm max-w-none">{!! nl2br(e($body)) !!}</div>
                    </div>
                @endif
            @endforeach

            @if (array_key_exists($tk->slug, \App\Support\TaskRef::KINDS))
                <livewire:member.relations-panel :kind="$tk->slug" :id="$task->id" :key="'rel-'.$tk->slug.'-'.$task->id" />
            @endif
        </div>
    </div>
</x-app-layout>
