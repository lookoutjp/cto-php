<div class="py-8">
    <div class="px-4 sm:px-6 lg:px-8">

        @if (session('status'))
            <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('status') }}</div>
        @endif

        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-xl font-bold text-gray-900">{{ $tk->label }}一覧</h1>
            <div class="flex items-center gap-2">
                @if ($tk->slug === 'routinework')
                    <a href="{{ route('routinework.generate') }}"
                       class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        マスターから生成
                    </a>
                @endif
                <a href="{{ route('tasks.create', $tk->slug) }}"
                   class="rounded-lg bg-brand px-4 py-2 text-sm font-medium text-brand-fg hover:bg-brand-dark">
                    新規起票
                </a>
            </div>
        </div>

        <div class="mb-4 flex flex-wrap gap-2">
            @foreach ($this->filters as $key => $label)
                <button type="button" wire:click="setView('{{ $key }}')"
                    @class([
                        'rounded-full px-3 py-1 text-xs font-medium transition',
                        'bg-brand text-brand-fg' => $view === $key,
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
                            <tr wire:key="task-{{ $task->id }}" class="hover:bg-gray-50">
                                <td class="whitespace-nowrap px-3 py-2 text-gray-500">{{ $task->id }}</td>
                                <td class="px-3 py-2">
                                    <div class="flex items-center gap-1.5">
                                        @if ($tk->has('today'))
                                            @php($isToday = $task->dotoday && $task->dotoday->isToday())
                                            <button type="button" wire:click="toggleToday({{ $task->id }})"
                                                    title="{{ $isToday ? '本日のタスクから外す' : '本日のタスクにする' }}"
                                                    class="shrink-0 text-sm {{ $isToday ? 'text-amber-500' : 'text-gray-300 hover:text-amber-400' }}">✪</button>
                                        @endif
                                        <a href="{{ route('tasks.show', [$tk->slug, $task->id]) }}"
                                           class="font-medium text-gray-900 hover:underline">{{ $task->title }}</a>
                                    </div>
                                    @if ($task->content)
                                        <p class="mt-0.5 max-w-md truncate text-xs text-gray-400">{{ \Illuminate\Support\Str::limit(strip_tags($task->content), 60) }}</p>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-3 py-2 text-gray-600">{{ optional($task->categoryModel)->categoryname ?? '—' }}</td>
                                <td class="whitespace-nowrap px-3 py-2">
                                    <select wire:change="quickUpdate({{ $task->id }}, 'status', $event.target.value)"
                                            class="rounded border-gray-200 bg-white py-1 pe-7 ps-2 text-xs text-gray-700 focus:border-gray-400 focus:ring-0">
                                        <option value="">—</option>
                                        @foreach ($statusOptions as $opt)
                                            <option value="{{ $opt->id }}" @selected((int) $task->status === (int) $opt->id)>{{ $opt->statusname }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="whitespace-nowrap px-3 py-2">
                                    <select wire:change="quickUpdate({{ $task->id }}, 'person_do', $event.target.value)"
                                            class="rounded border-gray-200 bg-white py-1 pe-7 ps-2 text-xs text-gray-700 focus:border-gray-400 focus:ring-0">
                                        <option value="">未設定</option>
                                        @foreach ($memberOptions as $m)
                                            <option value="{{ $m->member_id }}" @selected((string) $task->person_do === (string) $m->member_id)>{{ $m->name ?: $m->member_id }}</option>
                                        @endforeach
                                    </select>
                                </td>
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
