<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">掲示板</h2>
    </x-slot>

    <div class="py-8">
        <div class="space-y-4 px-4 sm:px-6 lg:px-8">

            @if ($siteBoard)
                <a href="{{ route('board.category', $siteBoard->id) }}"
                   class="block rounded-lg border border-gray-200 bg-white p-5 shadow-sm hover:border-gray-300">
                    <div class="flex items-baseline justify-between">
                        <span class="text-base font-semibold text-gray-900">{{ $siteBoard->displayName() }}</span>
                        <span class="text-xs text-gray-400">{{ $counts[$siteBoard->id] ?? 0 }} 件</span>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">サイト参加者向けの掲示板です。</p>
                </a>
            @endif

            <div>
                <h3 class="mb-2 mt-6 text-sm font-medium text-gray-500">コミュニティ</h3>
                @forelse ($communities as $c)
                    <a href="{{ route('board.category', $c->id) }}"
                       class="mb-2 block rounded-lg border border-gray-200 bg-white p-5 shadow-sm hover:border-gray-300">
                        <div class="flex items-baseline justify-between">
                            <span class="text-base font-semibold text-gray-900">{!! $c->displayName() !!}</span>
                            <span class="text-xs text-gray-400">{{ $counts[$c->id] ?? 0 }} 件</span>
                        </div>
                        @if ($c->intro)
                            <div class="prose prose-sm mt-1 max-w-none text-gray-500">{!! \Illuminate\Support\Str::limit(strip_tags($c->intro), 160) !!}</div>
                        @endif
                    </a>
                @empty
                    <p class="rounded-lg border border-gray-200 bg-white px-4 py-6 text-center text-sm text-gray-400">
                        コミュニティは開設されていません。
                    </p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
