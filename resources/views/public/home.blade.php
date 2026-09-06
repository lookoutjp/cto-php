@php
    $ownerLabel = trim((string) ($site?->manager_shouko ?: 'オーナー'));
    $ownerName = trim((string) $site?->webmanager);
@endphp

<x-layouts.public>
    @if ($site?->logo || $ownerName)
        <x-slot name="aside">
            <div class="rounded-lg border border-gray-200 bg-white p-5 text-center">
                @if ($site?->logo)
                    <img src="{{ Illuminate\Support\Str::startsWith($site->logo, ['http://', 'https://']) ? $site->logo : asset(trim($site->logo, '/')) }}"
                         alt="{{ $site->sitename }}"
                         @style([
                             'height: '.$site->logoheight.'px' => $site->logoheight,
                             'width: '.$site->logowidth.'px' => $site->logowidth,
                         ])
                         class="mx-auto max-w-full">
                @endif
                @if ($ownerName)
                    <p class="mt-3 text-sm text-gray-500">{{ $ownerLabel }}：{{ $ownerName }}</p>
                @endif
            </div>
        </x-slot>
    @endif

    {{-- ヒーローバナー --}}
    <section class="overflow-hidden rounded-lg bg-brand text-brand-fg">
        <div class="flex flex-col items-center gap-4 px-6 py-10 text-center sm:flex-row sm:justify-between sm:text-left">
            <div>
                <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">{{ $site?->sitename ?? config('app.name') }}</h1>
            </div>
            @if ($site?->homepagemainimage)
                <img src="{{ Illuminate\Support\Str::startsWith($site->homepagemainimage, ['http://', 'https://']) ? $site->homepagemainimage : asset(trim($site->homepagemainimage, '/')) }}"
                     alt="" class="h-20 w-auto shrink-0 opacity-95">
            @endif
        </div>
    </section>

    <div class="mt-6 space-y-8">
        {{-- 最新ニュースを先頭に --}}
        <section>
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

        {{-- おすすめ／人気コンテンツを左右2列に --}}
        @if ($recommended->isNotEmpty() || $popular->isNotEmpty())
            <div class="grid gap-6 md:grid-cols-2">
                @foreach (['おすすめコンテンツ' => $recommended, '人気コンテンツ' => $popular] as $heading => $list)
                    @if ($list->isNotEmpty())
                        <section>
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
            </div>
        @endif
    </div>
</x-layouts.public>
