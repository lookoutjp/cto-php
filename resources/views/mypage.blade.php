<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            マイページ
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="px-4 sm:px-6 lg:px-8">
        <p class="mb-4 text-sm text-gray-500">{{ $site?->sitename ?? config('app.name') }} &gt; マイページ</p>
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_260px]">
        <div class="order-1 space-y-6">

            <div class="rounded-lg bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">こんにちは</p>
                <p class="text-lg font-semibold text-gray-900">{{ $member->name ?: $member->getKey() }} さん</p>
            </div>

            @php($noPanels = count($todayTasks) === 0 && count($statusGrid) === 0 && ! $routineGrid)
            @if ($noPanels)
                <div class="rounded-lg bg-white p-5 text-sm text-gray-500 shadow-sm">
                    このサイトでは表示できる業務データがありません。
                </div>
            @endif

            {{-- 本日の計画作業 --}}
            @if (count($todayTasks))
            <section class="rounded-lg bg-white shadow-sm">
                <h3 class="border-b border-gray-100 px-5 py-3 font-semibold text-gray-900">本日の計画作業</h3>
                <div class="divide-y divide-gray-100">
                    @foreach ($todayTasks as $group)
                        <div class="flex gap-4 px-5 py-3">
                            <div class="w-20 shrink-0 pt-0.5 text-sm font-medium text-gray-500">{{ $group['label'] }}</div>
                            <div class="min-w-0 flex-1">
                                @forelse ($group['items'] as $item)
                                    <div class="flex items-baseline gap-2 py-0.5 text-sm">
                                        <span class="inline-block w-10 shrink-0 rounded bg-gray-100 text-center text-xs text-gray-500">{{ $item->id }}</span>
                                        <span class="text-gray-800">{{ $item->title }}</span>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-400">今日の計画データはありません。</p>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
            @endif

            {{-- 管理タスク対応状況 --}}
            @if (count($statusGrid))
            <section class="overflow-hidden rounded-lg bg-white shadow-sm">
                <h3 class="border-b border-gray-100 px-5 py-3 font-semibold text-gray-900">管理タスク対応状況</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-xs text-gray-500">
                                <th class="px-5 py-2 text-left font-medium"></th>
                                <th class="px-3 py-2 text-center font-medium">新規</th>
                                <th class="px-3 py-2 text-center font-medium">
                                    <span class="rounded bg-amber-100 px-2 py-0.5 text-amber-800">接近</span>
                                </th>
                                <th class="px-3 py-2 text-center font-medium">
                                    <span class="rounded bg-red-100 px-2 py-0.5 text-red-800">遅延</span>
                                </th>
                                <th class="px-3 py-2 text-center font-medium">
                                    <span class="rounded bg-gray-100 px-2 py-0.5 text-gray-600">期限未設定</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @php($linkable = ['todo', 'problem', 'risk', 'change'])
                            @foreach ($statusGrid as $row)
                                @php($canLink = in_array($row['view'], $linkable, true))
                                @php($cell = function ($bucket, $count) use ($row, $canLink) {
                                    return $canLink && $count > 0
                                        ? '<a href="'.route('tasks.index', ['kind' => $row['view'], 'view' => 'my'.$bucket]).'" class="block hover:underline">'.$count.'</a>'
                                        : $count;
                                })
                                <tr>
                                    <th class="px-5 py-3 text-left font-medium text-gray-700">{{ $row['label'] }}</th>
                                    <td class="px-3 py-3 text-center tabular-nums text-gray-900">{!! $cell('new', $row['new']) !!}</td>
                                    <td @class(['px-3 py-3 text-center tabular-nums', 'bg-amber-50 font-semibold text-amber-800' => $row['here'] > 0, 'text-gray-400' => $row['here'] === 0])>{!! $cell('here', $row['here']) !!}</td>
                                    <td @class(['px-3 py-3 text-center tabular-nums', 'bg-red-50 font-semibold text-red-800' => $row['late'] > 0, 'text-gray-400' => $row['late'] === 0])>{!! $cell('late', $row['late']) !!}</td>
                                    <td @class(['px-3 py-3 text-center tabular-nums', 'text-gray-700' => $row['nulldate'] > 0, 'text-gray-400' => $row['nulldate'] === 0])>{!! $cell('nulldate', $row['nulldate']) !!}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
            @endif

            {{-- 定例作業対応状況 --}}
            @if ($routineGrid)
            <section class="overflow-hidden rounded-lg bg-white shadow-sm">
                <h3 class="border-b border-gray-100 px-5 py-3 font-semibold text-gray-900">定例作業対応状況</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-xs text-gray-500">
                                <th class="px-3 py-2 text-center font-medium">新規</th>
                                <th class="px-3 py-2 text-center font-medium"><span class="rounded bg-amber-100 px-2 py-0.5 text-amber-800">接近</span></th>
                                <th class="px-3 py-2 text-center font-medium"><span class="rounded bg-red-100 px-2 py-0.5 text-red-800">遅延</span></th>
                                <th class="px-3 py-2 text-center font-medium">本日</th>
                                <th class="px-3 py-2 text-center font-medium"><span class="rounded bg-amber-100 px-2 py-0.5 text-amber-800">明日</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-t border-gray-100">
                                <td class="px-3 py-3 text-center tabular-nums text-gray-900">{{ $routineGrid['new'] }}</td>
                                <td @class(['px-3 py-3 text-center tabular-nums', 'bg-amber-50 font-semibold text-amber-800' => $routineGrid['here'] > 0, 'text-gray-400' => $routineGrid['here'] === 0])>{{ $routineGrid['here'] }}</td>
                                <td @class(['px-3 py-3 text-center tabular-nums', 'bg-red-50 font-semibold text-red-800' => $routineGrid['late'] > 0, 'text-gray-400' => $routineGrid['late'] === 0])>{{ $routineGrid['late'] }}</td>
                                <td class="px-3 py-3 text-center tabular-nums text-gray-900">{{ $routineGrid['today'] }}</td>
                                <td @class(['px-3 py-3 text-center tabular-nums', 'bg-amber-50 font-semibold text-amber-800' => $routineGrid['tomorrow'] > 0, 'text-gray-400' => $routineGrid['tomorrow'] === 0])>{{ $routineGrid['tomorrow'] }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
            @endif

        </div>

        <div class="order-2">
            @include('layouts.partials.my-menu')
        </div>

        </div>
        </div>
    </div>
</x-app-layout>
