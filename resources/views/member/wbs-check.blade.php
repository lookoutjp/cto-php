<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            <a href="{{ route('wbs.index') }}" class="text-gray-500 hover:underline">WBS</a>
            <span class="text-gray-400">/</span> 計画チェック
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl space-y-4 px-4 sm:px-6 lg:px-8">
            <p class="text-xs text-gray-500">
                サマリ項目の計画値と、その配下タスクの集計を比較します。
                <span class="rounded bg-red-100 px-1 text-red-700">超過</span>
                <span class="rounded bg-amber-100 px-1 text-amber-700">余裕</span>
                <span class="rounded bg-gray-100 px-1 text-gray-500">未計画（配下タスクなし）</span>
            </p>

            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500">
                            <tr>
                                <th class="px-3 py-2 text-left font-medium">サマリ項目</th>
                                <th class="px-3 py-2 text-right font-medium">計画工数</th>
                                <th class="px-3 py-2 text-right font-medium">配下合計</th>
                                <th class="px-3 py-2 text-center font-medium">計画開始</th>
                                <th class="px-3 py-2 text-center font-medium">配下最早</th>
                                <th class="px-3 py-2 text-center font-medium">計画完了</th>
                                <th class="px-3 py-2 text-center font-medium">配下最遅</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($rows as $r)
                                @php
                                    $daysCls = ! $r->has_tasks ? 'bg-gray-50 text-gray-400'
                                        : ((int) $r->actual_days > (int) $r->plan_days ? 'bg-red-50 text-red-700 font-semibold'
                                        : ((int) $r->actual_days < (int) $r->plan_days ? 'bg-amber-50 text-amber-700' : ''));
                                    $startCls = ! $r->has_tasks ? 'bg-gray-50 text-gray-400'
                                        : ($r->actual_start && $r->plan_start && $r->actual_start->lt($r->plan_start) ? 'bg-red-50 text-red-700 font-semibold'
                                        : ($r->actual_start && $r->plan_start && $r->actual_start->gt($r->plan_start) ? 'bg-amber-50 text-amber-700' : ''));
                                    $endCls = ! $r->has_tasks ? 'bg-gray-50 text-gray-400'
                                        : ($r->actual_end && $r->plan_end && $r->actual_end->gt($r->plan_end) ? 'bg-red-50 text-red-700 font-semibold'
                                        : ($r->actual_end && $r->plan_end && $r->actual_end->lt($r->plan_end) ? 'bg-amber-50 text-amber-700' : ''));
                                @endphp
                                <tr>
                                    <td class="px-3 py-2" style="padding-left: {{ 0.75 + ($r->node->deep - 1) * 1 }}rem">
                                        <a href="{{ route('wbs.show', $r->node->id) }}" class="text-gray-900 hover:underline">{{ $r->node->title }}</a>
                                    </td>
                                    <td class="px-3 py-2 text-right tabular-nums text-gray-600">{{ $r->plan_days ?: '—' }}</td>
                                    <td class="px-3 py-2 text-right tabular-nums {{ $daysCls }}">{{ $r->has_tasks ? $r->actual_days : '—' }}</td>
                                    <td class="px-3 py-2 text-center tabular-nums text-gray-600">{{ optional($r->plan_start)->isoFormat('YY/MM/DD') ?: '—' }}</td>
                                    <td class="px-3 py-2 text-center tabular-nums {{ $startCls }}">{{ optional($r->actual_start)->isoFormat('YY/MM/DD') ?: '—' }}</td>
                                    <td class="px-3 py-2 text-center tabular-nums text-gray-600">{{ optional($r->plan_end)->isoFormat('YY/MM/DD') ?: '—' }}</td>
                                    <td class="px-3 py-2 text-center tabular-nums {{ $endCls }}">{{ optional($r->actual_end)->isoFormat('YY/MM/DD') ?: '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-3 py-8 text-center text-sm text-gray-400">サマリ項目がありません。</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
