@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title.' | ' : '' }}{{ $site?->sitename ?? config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @include('partials.theme-style')
    @include('partials.favicon')
</head>
<body class="min-h-screen bg-gray-50 text-gray-900 antialiased flex flex-col">
    <div class="h-1 bg-brand"></div>

    @php($adminMode = $adminMode ?? false)
    @php($backUrl = urlencode(url()->full()))
    @if ($adminMode)
        <div class="bg-amber-500 px-4 py-1.5 text-center text-sm font-semibold text-white sm:px-6 lg:px-8">
            🛠 管理者モードで表示中です。メニュー・カテゴリ・記事・ニュース・FAQ・リンクなどを各ページから直接追加・編集できます。
            <form method="POST" action="{{ route('admin-mode.toggle') }}" class="inline">
                @csrf
                <button type="submit" class="ml-2 underline hover:no-underline">終了する</button>
            </form>
        </div>
    @endif

    @php($nav = array_filter([
        ['home', 'ホーム'],
        ['news.index', 'ニュース'],
        ['contents.index', 'コンテンツ'],
        ['faq.index', 'FAQ'],
        $site?->hasFunction('friendlinkfunction') ? ['links.index', 'リンク集'] : null,
        $site?->hasFunction('otoiawasefunction') ? ['contact.create', 'お問い合わせ'] : null,
    ]))
    <header class="border-b border-gray-200 bg-white" x-data="{ open: false }">
        <div class="flex items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                <x-site-logo :site="$site" class="h-12 w-auto" />
                @unless (trim((string) $site?->logo))
                    <span class="text-xl font-bold tracking-tight" style="color: var(--brand-name)">
                        {{ $site?->sitename ?? config('app.name') }}
                    </span>
                @endunless
            </a>

            {{-- デスクトップ: 旧 inc_top.asp 相当のトップメニュー（管理画面等のボタンより左側） --}}
            <nav class="hidden items-center gap-1 text-base sm:flex">
                @foreach ($topMenus ?? [] as $tm)
                    <span class="inline-flex items-center gap-1">
                        <a href="{{ \App\Support\LegacyLinkResolver::resolve($tm->linkaddress, $site, route('home')) }}"
                           @if ($tm->isExternal()) target="_blank" rel="noopener" @endif
                           class="rounded-md bg-brand px-3 py-2 font-medium text-brand-fg transition hover:bg-brand-dark">
                            {{ $tm->label() }}
                        </a>
                        @if ($adminMode)
                            <a href="{{ route('filament.admin.resources.top-menus.edit', $tm) }}?back={{ $backUrl }}"
                               class="rounded-md bg-white/90 p-1.5 text-brand hover:bg-white" title="「{{ $tm->label() }}」を編集">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                        @endif
                    </span>
                @endforeach

                @if ($adminMode)
                    <a href="{{ route('filament.admin.resources.top-menus.create') }}?back={{ $backUrl }}"
                       class="rounded-md border border-dashed border-amber-600 px-3 py-2 font-medium text-amber-700 hover:bg-amber-50">
                        ＋ メニューを追加
                    </a>
                @endif

                @auth
                    @php($me = auth()->user())
                    @if ($site && $me?->managesSite($site->site_id))
                        <a href="/admin" class="ml-2 rounded-md border border-gray-300 px-3 py-2 text-gray-700 hover:bg-gray-100">管理画面</a>
                        <form method="POST" action="{{ route('admin-mode.toggle') }}" class="ml-2">
                            @csrf
                            <button type="submit"
                                    @class([
                                        'rounded-md border px-3 py-2 transition',
                                        'border-amber-600 bg-amber-500 text-white hover:bg-amber-600' => $adminMode,
                                        'border-gray-300 text-gray-700 hover:bg-gray-100' => ! $adminMode,
                                    ])>
                                {{ $adminMode ? '管理者モード終了' : '管理者モード開始' }}
                            </button>
                        </form>
                    @endif

                    @if ($site?->hasFunction('dengonfunction'))
                        <a href="{{ route('messages.index') }}" class="ml-2 text-gray-400 hover:text-brand" title="メッセージ">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </a>
                    @endif

                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="ml-1 inline-flex items-center rounded-md border border-gray-300 px-3 py-2 text-base font-medium text-gray-700 transition hover:bg-gray-100">
                                <span>{{ $me->displayName() }}</span>
                                <svg class="ms-1 h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('dashboard')">マイページ</x-dropdown-link>
                            <x-dropdown-link :href="route('profile.edit')">プロフィール</x-dropdown-link>
                            <x-dropdown-link :href="route('site-join.index')">他サイトに加入</x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                                 onclick="event.preventDefault(); this.closest('form').submit();">
                                    ログアウト
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    @if ($site?->hasFunction('newmemberregfunction'))
                        <a href="{{ route('register') }}" class="ml-2 rounded-md border border-gray-300 px-3 py-2 text-gray-700 hover:bg-gray-100">会員登録</a>
                    @endif
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

        {{-- モバイル: 旧 inc_top.asp 相当のトップメニュー（管理画面等のリンクより上） --}}
        <nav x-show="open" x-cloak class="border-t border-gray-200 sm:hidden">
            <div class="space-y-1 py-2">
                @foreach ($topMenus ?? [] as $tm)
                    <a href="{{ \App\Support\LegacyLinkResolver::resolve($tm->linkaddress, $site, route('home')) }}"
                       @if ($tm->isExternal()) target="_blank" rel="noopener" @endif
                       class="block w-full border-l-4 border-transparent py-2 ps-3 pe-4 text-start text-base font-medium text-gray-600 transition duration-150 ease-in-out hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800 focus:border-gray-300 focus:bg-gray-50 focus:text-gray-800 focus:outline-none">
                        {{ $tm->label() }}
                    </a>
                @endforeach

                @if ($adminMode)
                    <x-responsive-nav-link href="{{ route('filament.admin.resources.top-menus.create') }}?back={{ $backUrl }}">
                        ＋ メニューを追加
                    </x-responsive-nav-link>
                @endif

                @auth
                    @php($me = auth()->user())
                    @if ($site && $me?->managesSite($site->site_id))
                        <x-responsive-nav-link href="/admin">管理画面</x-responsive-nav-link>
                        <form method="POST" action="{{ route('admin-mode.toggle') }}">
                            @csrf
                            <button type="submit" class="block w-full ps-3 pe-4 py-2 text-start text-base font-medium text-amber-700 hover:bg-amber-50">
                                {{ $adminMode ? '🛠 管理者モード終了' : '🛠 管理者モード開始' }}
                            </button>
                        </form>
                    @endif

                    <div class="border-t border-gray-200 px-3 pt-3 pb-1 text-sm font-medium text-gray-500">
                        {{ $me->displayName() }}
                    </div>
                    @if ($site?->hasFunction('dengonfunction'))
                        <x-responsive-nav-link :href="route('messages.index')">メッセージ</x-responsive-nav-link>
                    @endif
                    <x-responsive-nav-link href="{{ route('dashboard') }}">マイページ</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('profile.edit')">プロフィール</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('site-join.index')">他サイトに加入</x-responsive-nav-link>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('logout')"
                                               onclick="event.preventDefault(); this.closest('form').submit();">
                            ログアウト
                        </x-responsive-nav-link>
                    </form>
                @else
                    @if ($site?->hasFunction('newmemberregfunction'))
                        <x-responsive-nav-link :href="route('register')">会員登録</x-responsive-nav-link>
                    @endif
                    <x-responsive-nav-link :href="route('login')">ログイン</x-responsive-nav-link>
                @endauth
            </div>
        </nav>
    </header>

    @php($sidebarCategories = $sidebarCategories ?? collect())
    @php($showSidebar = $sidebarCategories->isNotEmpty() || $adminMode)
    <main class="w-full flex-1 px-4 py-8 sm:px-6 lg:px-8">
        <div @class([
            'grid grid-cols-1 gap-6',
            'lg:grid-cols-[250px_minmax(0,1fr)_220px]' => isset($aside) && $showSidebar,
            'lg:grid-cols-[250px_minmax(0,1fr)]' => ! isset($aside) && $showSidebar,
            'lg:grid-cols-[minmax(0,1fr)_220px]' => isset($aside) && ! $showSidebar,
        ])>
            {{-- 旧 inc_left.asp 相当の左サイドバー「カテゴリ」（content_sorts のトップレベル）。全ページ共通 --}}
            @if ($showSidebar)
                @php($activeCategoryId = request()->routeIs('contents.index') ? request()->integer('category') : null)
                <aside class="order-2 lg:order-1">
                    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
                        <h2 class="bg-brand-bg px-4 py-2 text-base font-semibold text-brand">カテゴリ</h2>
                        <ul id="sidebar-category-list" data-admin-mode="{{ $adminMode ? '1' : '0' }}" class="divide-y divide-gray-100 text-base">
                            @foreach ($sidebarCategories as $cat)
                                <li data-id="{{ $cat->id }}" @class(['flex items-center justify-between' => $adminMode])>
                                    @if ($adminMode)
                                        <span class="category-drag-handle cursor-move p-2 text-gray-300 hover:text-gray-500" title="ドラッグで並び替え">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                                            </svg>
                                        </span>
                                    @endif
                                    <a href="{{ \App\Support\LegacyLinkResolver::resolve($cat->link, $site, route('contents.index', ['category' => $cat->id])) }}"
                                       @class([
                                           'block px-4 py-2 hover:bg-gray-50 hover:text-brand',
                                           'flex-1' => $adminMode,
                                           'bg-brand-bg font-medium text-brand' => $activeCategoryId === $cat->id,
                                           'text-gray-700' => $activeCategoryId !== $cat->id,
                                       ])>
                                        {{ $cat->name }}
                                    </a>
                                    @if ($adminMode)
                                        <a href="{{ route('filament.admin.resources.content-sorts.edit', $cat) }}?back={{ $backUrl }}"
                                           class="mr-2 rounded-md p-1 text-gray-400 hover:bg-gray-100 hover:text-brand" title="「{{ $cat->name }}」を編集">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                        @if ($adminMode)
                            <p id="category-sort-note" class="hidden px-4 py-1 text-xs text-gray-500"></p>
                            <a href="{{ route('filament.admin.resources.content-sorts.create') }}?back={{ $backUrl }}"
                               class="block border-t border-dashed border-amber-300 px-4 py-2 text-sm font-medium text-amber-700 hover:bg-amber-50">
                                ＋ カテゴリを追加
                            </a>
                        @endif
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
        <div class="space-y-4 px-4 py-6 text-center text-sm text-gray-500 sm:px-6 lg:px-8">
            <nav class="flex flex-wrap justify-center gap-x-6 gap-y-2 text-base font-medium">
                @foreach ($nav as [$routeName, $label])
                    <a href="{{ route($routeName) }}"
                       @class([
                           'text-brand' => request()->routeIs($routeName),
                           'text-gray-600 hover:text-gray-900' => ! request()->routeIs($routeName),
                       ])>{{ $label }}</a>
                @endforeach
            </nav>

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
