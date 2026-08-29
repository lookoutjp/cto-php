<x-layouts.public>
    <section class="rounded-lg border border-gray-200 bg-white p-8">
        <h1 class="text-2xl font-bold tracking-tight text-gray-900">
            {{ $site?->sitename ?? config('app.name') }}
        </h1>
        @if ($site?->siteintro)
            <div class="prose prose-sm mt-4 max-w-none text-gray-700">
                {!! $site->siteintro !!}
            </div>
        @endif
    </section>

    @foreach (['おすすめコンテンツ' => $recommended, '人気コンテンツ' => $popular] as $heading => $list)
        @if ($list->isNotEmpty())
            <section class="mt-8">
                <h2 class="mb-3 text-lg font-semibold text-gray-900">{{ $heading }}</h2>
                <ul class="divide-y divide-gray-100 overflow-hidden rounded-lg border border-gray-200 bg-white">
                    @foreach ($list as $c)
                        <li class="px-4 py-3">
                            <a href="{{ route('contents.show', $c) }}" class="font-medium text-gray-900 hover:text-gray-600 hover:underline">{{ $c->name }}</a>
                            @if ($c->introduce)
                                <p class="mt-0.5 truncate text-sm text-gray-500">{{ strip_tags($c->introduce) }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    @endforeach

    <section class="mt-8">
        <div class="mb-3 flex items-baseline justify-between">
            <h2 class="text-lg font-semibold text-gray-900">最新ニュース</h2>
            <a href="{{ route('news.index') }}" class="text-sm text-gray-600 hover:text-gray-900 hover:underline">すべて見る</a>
        </div>

        @if ($latestNews->isEmpty())
            <p class="rounded-lg border border-gray-200 bg-white px-4 py-6 text-center text-sm text-gray-500">
                ニュースはまだありません。
            </p>
        @else
            <ul class="divide-y divide-gray-100 overflow-hidden rounded-lg border border-gray-200 bg-white">
                @foreach ($latestNews as $item)
                    <li class="flex items-baseline gap-3 px-4 py-3">
                        <time class="shrink-0 text-sm tabular-nums text-gray-500">
                            {{ optional($item->newsdate)->isoFormat('YYYY/MM/DD') }}
                        </time>
                        @if ($item->isPinned())
                            <span class="rounded bg-red-50 px-1.5 py-0.5 text-xs font-medium text-red-600">重要</span>
                        @endif
                        <a href="{{ route('news.show', $item) }}" class="truncate font-medium text-gray-900 hover:text-gray-600 hover:underline">
                            {{ $item->title }}
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</x-layouts.public>
