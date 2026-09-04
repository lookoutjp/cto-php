@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title.' | ' : '' }}{{ $site?->sitename ?? config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @include('partials.theme-style')
</head>
<body class="min-h-screen bg-gray-50 text-gray-900 antialiased flex flex-col">
    <div class="h-1 bg-brand"></div>

    {{-- 旧 inc_top.asp 相当のトップメニュー（top_menus）。運営者が Filament で登録した場合のみ表示 --}}
    @if (($topMenus ?? collect())->isNotEmpty())
        <div class="bg-brand-bg">
            <nav class="mx-auto flex max-w-6xl flex-wrap items-center justify-end gap-2 px-4 py-2">
                @foreach ($topMenus as $tm)
                    <a href="{{ \App\Support\LegacyLinkResolver::resolve($tm->linkaddress, $site, route('home')) }}"
                       @if ($tm->isExternal()) target="_blank" rel="noopener" @endif
                       class="rounded-md bg-brand px-3 py-1.5 text-sm font-medium text-brand-fg transition hover:bg-brand-dark">
                        {{ $tm->label() }}
                    </a>
                @endforeach
            </nav>
        </div>
    @endif

    @php($nav = array_filter([
        ['home', 'ホーム'],
        ['news.index', 'ニュース'],
        ['contents.index', 'コンテンツ'],
        ['faq.index', 'FAQ'],
        $site?->hasFunction('managerwordsfunction') ? ['manager-words', $site->manager_shouko ? $site->manager_shouko.'の言葉' : '管理員の言葉'] : null,
        $site?->hasFunction('friendlinkfunction') ? ['links.index', 'リンク集'] : null,
        $site?->hasFunction('otoiawasefunction') ? ['contact.create', 'お問い合わせ'] : null,
    ]))
    <header class="border-b border-gray-200 bg-white" x-data="{ open: false }">
        <div class="mx-auto flex max-w-5xl items-center justify-between gap-4 px-4 py-4">
            <a href="{{ route('home') }}" class="text-lg font-bold tracking-tight" style="color: var(--brand-name)">
                {{ $site?->sitename ?? config('app.name') }}
            </a>

            {{-- デスクトップ: 横並びナビ --}}
            <nav class="hidden items-center gap-1 text-sm sm:flex">
                @foreach ($nav as [$routeName, $label])
                    <a href="{{ route($routeName) }}"
                       @class([
                           'rounded-md px-3 py-2 transition',
                           'bg-brand text-brand-fg' => request()->routeIs($routeName),
                           'text-gray-600 hover:bg-gray-100 hover:text-gray-900' => ! request()->routeIs($routeName),
                       ])>{{ $label }}</a>
                @endforeach

                @auth
                    <a href="{{ url('/dashboard') }}" class="ml-2 rounded-md border border-gray-300 px-3 py-2 text-gray-700 hover:bg-gray-100">マイページ</a>
                @else
                    <a href="{{ route('login') }}" class="ml-2 rounded-md border border-gray-300 px-3 py-2 text-gray-700 hover:bg-gray-100">ログイン</a>
                @endauth
            </nav>

            {{-- モバイル: ハンバーガーボタン --}}
            <button type="button" @click="open = ! open" aria-label="メニュー"
                    class="inline-flex items-center justify-center rounded-md p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700 focus:outline-none sm:hidden">
                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                    <path :class="{ 'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    <path :class="{ 'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- モバイル: 展開メニュー --}}
        <nav x-show="open" x-cloak class="border-t border-gray-200 sm:hidden">
            <div class="space-y-1 py-2">
                @foreach ($nav as [$routeName, $label])
                    <x-responsive-nav-link :href="route($routeName)" :active="request()->routeIs($routeName)">
                        {{ $label }}
                    </x-responsive-nav-link>
                @endforeach

                @auth
                    <x-responsive-nav-link href="{{ url('/dashboard') }}">マイページ</x-responsive-nav-link>
                @else
                    <x-responsive-nav-link :href="route('login')">ログイン</x-responsive-nav-link>
                @endauth
            </div>
        </nav>
    </header>

    @php($sidebarCategories = $sidebarCategories ?? collect())
    <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-8">
        <div @class([
            'grid grid-cols-1 gap-6',
            'lg:grid-cols-[190px_minmax(0,1fr)_220px]' => isset($aside) && $sidebarCategories->isNotEmpty(),
            'lg:grid-cols-[190px_minmax(0,1fr)]' => ! isset($aside) && $sidebarCategories->isNotEmpty(),
            'lg:grid-cols-[minmax(0,1fr)_220px]' => isset($aside) && $sidebarCategories->isEmpty(),
        ])>
            {{-- 旧 inc_left.asp 相当の左サイドバー「カテゴリ」（content_sorts のトップレベル）。全ページ共通 --}}
            @if ($sidebarCategories->isNotEmpty())
                @php($activeCategoryId = request()->routeIs('contents.index') ? request()->integer('category') : null)
                <aside class="order-2 lg:order-1">
                    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
                        <h2 class="bg-brand-bg px-4 py-2 text-sm font-semibold text-brand">カテゴリ</h2>
                        <ul class="divide-y divide-gray-100 text-sm">
                            @foreach ($sidebarCategories as $cat)
                                <li>
                                    <a href="{{ \App\Support\LegacyLinkResolver::resolve($cat->link, $site, route('contents.index', ['category' => $cat->id])) }}"
                                       @class([
                                           'block px-4 py-2 hover:bg-gray-50 hover:text-brand',
                                           'bg-brand-bg font-medium text-brand' => $activeCategoryId === $cat->id,
                                           'text-gray-700' => $activeCategoryId !== $cat->id,
                                       ])>
                                        {{ $cat->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </aside>
            @endif

            <div class="order-1 min-w-0 lg:order-2">
                @isset($title)
                    <h1 class="mb-6 text-2xl font-bold tracking-tight text-gray-900">{{ $title }}</h1>
                @endisset

                {{ $slot }}
            </div>

            @isset($aside)
                <aside class="order-3">
                    {{ $aside }}
                </aside>
            @endisset
        </div>
    </main>

    <footer class="border-t border-gray-200 bg-white">
        <div class="mx-auto max-w-5xl space-y-2 px-4 py-6 text-center text-xs text-gray-500">
            <nav class="flex flex-wrap justify-center gap-x-4 gap-y-1">
                <a href="{{ route('legal.terms') }}" class="hover:text-gray-900">利用規約</a>
                <a href="{{ route('legal.privacy') }}" class="hover:text-gray-900">プライバシーポリシー</a>
                <a href="{{ route('legal.tokushoho') }}" class="hover:text-gray-900">特定商取引法に基づく表記</a>
            </nav>
            <div>&copy; {{ now()->year }} {{ $site?->sitename ?? config('app.name') }}</div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
