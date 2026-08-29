<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            <a href="{{ route('wbs.index') }}" class="text-gray-500 hover:underline">WBS</a>
            <span class="text-gray-400">/</span> スケジュール計算
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-6xl space-y-4 px-4 sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('status') }}</div>
            @endif
            @if (session('error') || $error)
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') ?? $error }}</div>
            @endif

            <p class="text-xs text-gray-500">
                先行→後続の依存（FS/SS/FF/SF）とラグを考慮し、各タスクの期間（所要日数）から
                最早開始・最早完了を計算します。フロート 0 以下が
                <span class="rounded bg-red-100 px-1 text-red-700">クリティカルパス</span> です。
                @if ($rootTitle)
                    <br>対象: <span class="font-medium text-gray-700">{{ $rootTitle }}</span> 配下
                    ・<a href="{{ route('wbs.schedule') }}" class="text-gray-500 underline">全体を表示</a>
                @endif
            </p>

            <div class="flex flex-wrap items-center gap-3 text-xs">
                <span class="text-gray-500">日数の数え方:</span>
                @foreach (['working' => '稼働日（土日・休日を除外）', 'calendar' => '暦日'] as $m => $ml)
                    <a href="{{ route('wbs.schedule', array_filter(['root' => $rootId, 'calendar' => $m === 'working' ? null : $m])) }}"
                       @class(['rounded-full px-3 py-1', 'bg-brand text-brand-fg' => $calMode === $m, 'bg-white text-gray-600 ring-1 ring-gray-200' => $calMode !== $m])>{{ $ml }}</a>
                @endforeach
                <a href="{{ route('wbs.holidays') }}" class="ml-auto text-gray-500 underline">休日カレンダーを編集</a>
            </div>

            @if ($result)
                <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-sm">
                            <thead class="bg-gray-50 text-xs text-gray-500">
                                <tr>
                                    <th class="px-3 py-2 text-left font-medium">タスク</th>
                                    <th class="px-3 py-2 text-right font-medium">所要日数</th>
                                    <th class="px-3 py-2 text-center font-medium">現在<br>開始/完了</th>
                                    <th class="px-3 py-2 text-center font-medium">計算<br>開始/完了</th>
                                    <th class="px-3 py-2 text-right font-medium">フロート</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($all as $w)
                                    @php($n = $result['nodes'][$w->id] ?? null)
                                    @php($ru = $result['rollup'][$w->id] ?? null)
                                    <tr class="{{ ($n && $n['critical']) ? 'bg-red-50' : '' }}">
                                        <td class="px-3 py-2" style="padding-left: {{ 0.75 + ($w->deep - 1) * 1 }}rem">
                                            <a href="{{ route('wbs.show', $w->id) }}" class="{{ $w->iscategory ? 'font-semibold text-gray-900' : 'text-gray-700' }} hover:underline">{{ $w->title }}</a>
                                            @if ($n && $n['critical'])
                                                <span class="ml-1 rounded bg-red-100 px-1 text-[10px] text-red-700">CP</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 text-right tabular-nums text-gray-500">{{ $w->iscategory ? '—' : ($w->tododays ?: 0) }}</td>
                                        <td class="px-3 py-2 text-center tabular-nums text-xs text-gray-500">
                                            {{ optional($w->godate)->isoFormat('YY/MM/DD') ?: '—' }}<br>{{ optional($w->duedate)->isoFormat('YY/MM/DD') ?: '—' }}
                                        </td>
                                        <td class="px-3 py-2 text-center tabular-nums text-xs">
                                            @if ($n)
                                                @php($chg = optional($w->godate)->toDateString() !== $n['es']->toDateString() || optional($w->duedate)->toDateString() !== $n['ef']->toDateString())
                                                <span class="{{ $chg ? 'font-semibold text-blue-700' : 'text-gray-700' }}">
                                                    {{ $n['es']->isoFormat('YY/MM/DD') }}<br>{{ $n['ef']->isoFormat('YY/MM/DD') }}
                                                </span>
                                            @elseif ($ru && $ru['start'])
                                                <span class="text-gray-500">{{ $ru['start']->isoFormat('YY/MM/DD') }}<br>{{ $ru['end']->isoFormat('YY/MM/DD') }}</span>
                                            @else
                                                <span class="text-gray-300">—</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 text-right tabular-nums {{ ($n && $n['float'] === 0) ? 'font-semibold text-red-700' : 'text-gray-500' }}">
                                            {{ $n ? $n['float'].'日' : '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <form method="post" action="{{ route('wbs.schedule.apply', $rootId ? ['root' => $rootId] : []) }}"
                      onsubmit="return confirm('計算結果を各タスクの着手予定・期限に書き戻します。よろしいですか？')"
                      class="flex items-center gap-4 rounded-lg bg-white p-4 shadow-sm">
                    @csrf
                    <input type="hidden" name="calendar" value="{{ $calMode }}">
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="update_summaries" value="1" class="rounded border-gray-300 text-gray-900 focus:ring-gray-500">
                        サマリ項目の日付も配下から更新する
                    </label>
                    <button type="submit" class="ml-auto rounded-lg bg-brand px-5 py-2 text-sm font-medium text-brand-fg hover:bg-brand-dark">
                        計算結果を反映
                    </button>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
