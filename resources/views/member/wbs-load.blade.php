<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            <a href="{{ route('wbs.index') }}" class="text-gray-500 hover:underline">WBS</a>
            <span class="text-gray-400">/</span> 負荷分析
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="space-y-4 px-4 sm:px-6 lg:px-8">

            <p class="text-xs text-gray-500">
                各リーフ WBS の所要日数を期間（着手予定〜期限）の稼働日に均等配分し、担当者 × 週 で合計した負荷です。
                週あたり <span class="font-medium text-gray-700">{{ $capacity }}</span> 日を超える週を
                <span class="rounded bg-red-100 px-1 text-red-700">過負荷</span> として表示します。
            </p>

            <form method="get" class="flex items-center gap-2 text-xs text-gray-500">
                <label>週あたり稼働可能日数</label>
                @foreach ([3, 4, 5, 6] as $c)
                    <a href="{{ route('wbs.load', ['capacity' => $c]) }}"
                       @class(['rounded-full px-3 py-1', 'bg-brand text-brand-fg' => (int) $capacity === $c, 'bg-white text-gray-600 ring-1 ring-gray-200' => (int) $capacity !== $c])>{{ $c }}</a>
                @endforeach
            </form>

            @if (empty($result['rows']))
                <div class="rounded-lg bg-white p-8 text-center text-sm text-gray-400 shadow-sm">
                    期間（着手予定・期限）と担当者・所要日数がそろった WBS がありません。
                </div>
            @else
                <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-full border-collapse text-xs">
                            <thead class="bg-gray-50 text-gray-500">
                                <tr>
                                    <th class="sticky left-0 z-10 bg-gray-50 px-3 py-2 text-left font-medium">担当者</th>
                                    @foreach ($result['weeks'] as $wk)
                                        <th class="px-2 py-2 text-center font-medium whitespace-nowrap">{{ \Illuminate\Support\Str::of($wk)->replace('-', ' ') }}</th>
                                    @endforeach
                                    <th class="px-3 py-2 text-right font-medium">計</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($result['rows'] as $row)
                                    <tr>
                                        <th class="sticky left-0 z-10 bg-white px-3 py-2 text-left font-medium text-gray-700 whitespace-nowrap">
                                            {{ $row['name'] }}
                                            @if ($row['overloaded'])
                                                <span class="ml-1 rounded bg-red-100 px-1 text-[10px] text-red-700">過負荷</span>
                                            @endif
                                        </th>
                                        @foreach ($result['weeks'] as $wk)
                                            @php($v = $row['cells'][$wk] ?? 0)
                                            @php($ratio = $capacity > 0 ? $v / $capacity : 0)
                                            <td @class([
                                                    'px-2 py-2 text-center tabular-nums',
                                                    'text-gray-300' => $v <= 0,
                                                    'text-gray-700' => $v > 0 && $ratio <= 0.75,
                                                    'bg-amber-50 text-amber-800' => $ratio > 0.75 && $ratio <= 1.0,
                                                    'bg-red-100 font-semibold text-red-800' => $ratio > 1.0,
                                                ])>{{ $v > 0 ? $v : '' }}</td>
                                        @endforeach
                                        <td class="px-3 py-2 text-right tabular-nums text-gray-500">{{ $row['total'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                @if (! empty($result['overloads']))
                    <div class="rounded-lg bg-white p-5 shadow-sm">
                        <h3 class="mb-3 text-sm font-semibold text-gray-900">過負荷の週と内訳</h3>
                        <ul class="space-y-3 text-sm">
                            @foreach ($result['overloads'] as $o)
                                <li>
                                    <div class="flex items-baseline gap-2">
                                        <span class="font-medium text-gray-800">{{ $o['name'] }}</span>
                                        <span class="text-gray-500">{{ \Illuminate\Support\Str::of($o['week'])->replace('-', ' ') }}</span>
                                        <span class="rounded bg-red-100 px-1.5 text-xs text-red-700">{{ $o['load'] }} 日 / {{ $capacity }} 日</span>
                                    </div>
                                    <ul class="mt-1 ml-4 list-disc text-xs text-gray-600">
                                        @foreach ($o['tasks'] as $t)
                                            <li>
                                                <a href="{{ route('wbs.show', $t['id']) }}" class="hover:underline">{{ $t['title'] }}</a>
                                                <span class="text-gray-400">（この週 {{ $t['days'] }} 日ぶん）</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </li>
                            @endforeach
                        </ul>
                        <p class="mt-3 text-xs text-gray-400">
                            フロートに余裕のあるタスク（スケジュール計算のクリティカルパス以外）を後ろにずらすと平準化できます。
                        </p>
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
