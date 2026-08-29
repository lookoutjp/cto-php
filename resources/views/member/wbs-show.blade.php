<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            <a href="{{ route('wbs.index') }}" class="text-gray-500 hover:underline">WBS</a>
            <span class="text-gray-400">/</span> #{{ $node->id }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('status') }}</div>
            @endif

            <div class="rounded-lg bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        @if ($node->parent)
                            <a href="{{ route('wbs.show', $node->parent->id) }}" class="text-xs text-gray-500 hover:underline">
                                &larr; {{ $node->parent->title }}
                            </a>
                        @endif
                        <h1 class="mt-1 text-lg font-bold text-gray-900">
                            {{ $node->title }}
                            @if ($node->iscategory)
                                <span class="ml-2 rounded bg-gray-100 px-1.5 py-0.5 align-middle text-xs font-normal text-gray-500">サマリ</span>
                            @endif
                        </h1>
                    </div>
                    <div class="flex shrink-0 gap-2">
                        <a href="{{ route('wbs.create', ['parent' => $node->id]) }}" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50">子項目を追加</a>
                        <a href="{{ route('wbs.edit', $node->id) }}" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50">編集</a>
                        <form method="post" action="{{ route('wbs.destroy', $node->id) }}" onsubmit="return confirm('削除しますか？')">
                            @csrf @method('DELETE')
                            <button class="rounded-lg border border-red-300 px-3 py-1.5 text-sm text-red-700 hover:bg-red-50">削除</button>
                        </form>
                    </div>
                </div>

                <dl class="mt-5 grid grid-cols-1 gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
                    @php
                        $rows = [
                            ['ステータス', optional($node->statusMaster)->statusname ?? '—'],
                            ['担当者', optional($node->assignee)->name ?? '未設定'],
                            ['主管チーム', optional($node->team)->levelname ?? '未設定'],
                            ['開始予定', optional($node->start_date)->isoFormat('YYYY年M月D日') ?? '—'],
                            ['完了予定', optional($node->complete_date)->isoFormat('YYYY年M月D日') ?? '—'],
                            ['起票者', optional($node->creator)->name ?? $node->maker ?? '—'],
                            ['更新日', optional($node->renewdate)->isoFormat('YYYY年M月D日') ?? '—'],
                        ];
                    @endphp
                    <div class="flex gap-2">
                        <dt class="w-24 shrink-0 text-gray-500">期限</dt>
                        <dd class="{{ $node->isOverdue() ? 'font-semibold text-red-600' : 'text-gray-900' }}">
                            {{ optional($node->duedate)->isoFormat('YYYY年M月D日') ?? '未設定' }}
                        </dd>
                    </div>
                    @foreach ($rows as [$label, $value])
                        <div class="flex gap-2"><dt class="w-24 shrink-0 text-gray-500">{{ $label }}</dt><dd class="text-gray-900">{{ $value }}</dd></div>
                    @endforeach
                </dl>
            </div>

            @if (filled($node->content))
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="mb-2 text-sm font-semibold text-gray-500">内容</h3>
                    <div class="prose prose-sm max-w-none">{!! nl2br(e($node->content)) !!}</div>
                </div>
            @endif
            @if (filled($node->situation))
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="mb-2 text-sm font-semibold text-gray-500">状況</h3>
                    <div class="prose prose-sm max-w-none">{!! nl2br(e($node->situation)) !!}</div>
                </div>
            @endif

            <livewire:member.relations-panel kind="wbs" :id="$node->id" :key="'rel-wbs-'.$node->id" />

            @if ($children->isNotEmpty())
                <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                    <h3 class="border-b border-gray-100 px-5 py-3 text-sm font-semibold text-gray-500">子タスク</h3>
                    <ul class="divide-y divide-gray-100">
                        @foreach ($children as $c)
                            <li class="flex items-center gap-3 px-5 py-2.5">
                                <a href="{{ route('wbs.show', $c->id) }}" class="min-w-0 flex-1 truncate text-gray-800 hover:underline">{{ $c->title }}</a>
                                <span class="shrink-0 text-xs text-gray-400">{{ optional($c->statusMaster)->statusname }}</span>
                                <span class="shrink-0 text-xs text-gray-400">{{ optional($c->assignee)->name }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
