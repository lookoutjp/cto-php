<div class="py-8">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">

        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-xl font-bold text-gray-900">{{ $tk->label }}一覧</h1>
            <a href="{{ route('tasks.create', $tk->slug) }}"
               class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                新規起票
            </a>
        </div>

        <div class="mb-4 flex flex-wrap gap-2">
            @foreach ($this->filters as $key => $label)
                <button type="button" wire:click="setView('{{ $key }}')"
                    @class([
                        'rounded-full px-3 py-1 text-xs font-medium transition',
                        'bg-gray-900 text-white' => $view === $key,
                        'bg-white text-gray-600 ring-1 ring-gray-200 hover:bg-gray-50' => $view !== $key,
                    ])>{{ $label }}</button>
            @endforeach
        </div>

        <div class="mb-4">
            <input type="search" wire:model.live.debounce.400ms="keyword" placeholder="タイトル・内容で検索"
                   class="w-full max-w-sm rounded-lg border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500">
        </div>

        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500">
                        <tr>
                            <th class="px-3 py-2 text-left font-medium"><button wire:click="sortBy('id')" class="hover:text-gray-900">#</button></th>
                            <th class="px-3 py-2 text-left font-medium"><button wire:click="sortBy('title')" class="hover:text-gray-900">タイトル</button></th>
                            <th class="px-3 py-2 text-left font-medium">分類</th>
                            <th class="px-3 py-2 text-left font-medium"><button wire:click="sortBy('status')" class="hover:text-gray-900">ステータス</button></th>
                            <th class="px-3 py-2 text-left font-medium"><button wire:click="sortBy('person_do')" class="hover:text-gray-900">担当者</button></th>
                            @if ($tk->has('team'))
                                <th class="px-3 py-2 text-left font-medium">主管チーム</th>
                            @endif
                            @if ($tk->has('date'))
                                <th class="px-3 py-2 text-left font-medium"><button wire:click="sortBy('{{ $tk->dateColumn() }}')" class="hover:text-gray-900">{{ $tk->dateLabel }}</button></th>
                            @endif
                            <th class="px-3 py-2 text-left font-medium"><button wire:click="sortBy('renewdate')" class="hover:text-gray-900">更新日</button></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($tasks as $task)
                            <tr class="hover:bg-gray-50">
                                <td class="whitespace-nowrap px-3 py-2 text-gray-500">{{ $task->id }}</td>
                                <td class="px-3 py-2">
                                    <a href="{{ route('tasks.show', [$tk->slug, $task->id]) }}"
                                       class="font-medium text-gray-900 hover:underline">{{ $task->title }}</a>
                                    @if ($task->content)
                                        <p class="mt-0.5 max-w-md truncate text-xs text-gray-400">{{ \Illuminate\Support\Str::limit(strip_tags($task->content), 60) }}</p>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-3 py-2 text-gray-600">{{ optional($task->categoryModel)->categoryname ?? '—' }}</td>
                                <td class="whitespace-nowrap px-3 py-2">
                                    @php($st = optional($task->statusMaster))
                                    <span @class([
                                        'rounded px-1.5 py-0.5 text-xs',
                                        'bg-gray-100 text-gray-600' => (int) $st->percent === 0,
                                        'bg-blue-100 text-blue-700' => $st->percent > 0 && $st->percent < 100,
                                        'bg-green-100 text-green-700' => (int) $st->percent === 100,
                                        'bg-amber-100 text-amber-700' => in_array((int) $st->percent, [-1, -2], true),
                                    ])>{{ $st->statusname ?? '—' }}</span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-2 text-gray-600">{{ optional($task->assignee)->name ?? '未設定' }}</td>
                                @if ($tk->has('team'))
                                    <td class="whitespace-nowrap px-3 py-2 text-gray-600">{{ optional($task->team)->levelname ?? '未設定' }}</td>
                                @endif
                                @if ($tk->has('date'))
                                    <td class="whitespace-nowrap px-3 py-2 {{ $task->isOverdue() ? 'font-semibold text-red-600' : 'text-gray-600' }}">
                                        {{ optional($task->{$tk->dateColumn()})->isoFormat('YYYY/MM/DD') ?? '未設定' }}
                                    </td>
                                @endif
                                <td class="whitespace-nowrap px-3 py-2 text-gray-400">{{ optional($task->renewdate)->isoFormat('YYYY/MM/DD') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-3 py-8 text-center text-sm text-gray-400">該当するデータはありません。</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">{{ $tasks->links() }}</div>
    </div>
</div>
