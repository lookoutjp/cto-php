@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title.' | ' : '' }}{{ $site?->sitename ?? config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-gray-50 text-gray-900 antialiased flex flex-col">
    <header class="border-b border-gray-200 bg-white">
        <div class="mx-auto flex max-w-5xl items-center justify-between gap-4 px-4 py-4">
            <a href="{{ route('home') }}" class="text-lg font-bold tracking-tight text-gray-900">
                {{ $site?->sitename ?? config('app.name') }}
            </a>
            <nav class="flex items-center gap-1 text-sm">
                @php($nav = array_filter([
                    ['home', 'ホーム'],
                    ['news.index', 'ニュース'],
                    ['contents.index', 'コンテンツ'],
                    ['faq.index', 'FAQ'],
                    $site?->hasFunction('otoiawasefunction') ? ['contact.create', 'お問い合わせ'] : null,
                ]))
                @foreach ($nav as [$routeName, $label])
                    <a href="{{ route($routeName) }}"
                       @class([
                           'rounded-md px-3 py-2 transition',
                           'bg-gray-900 text-white' => request()->routeIs($routeName),
                           'text-gray-600 hover:bg-gray-100 hover:text-gray-900' => ! request()->routeIs($routeName),
                       ])>{{ $label }}</a>
                @endforeach

                @auth
                    <a href="{{ url('/dashboard') }}" class="ml-2 rounded-md border border-gray-300 px-3 py-2 text-gray-700 hover:bg-gray-100">マイページ</a>
                @else
                    <a href="{{ route('login') }}" class="ml-2 rounded-md border border-gray-300 px-3 py-2 text-gray-700 hover:bg-gray-100">ログイン</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="mx-auto w-full max-w-5xl flex-1 px-4 py-8">
        @isset($title)
            <h1 class="mb-6 text-2xl font-bold tracking-tight text-gray-900">{{ $title }}</h1>
        @endisset

        {{ $slot }}
    </main>

    <footer class="border-t border-gray-200 bg-white">
        <div class="mx-auto max-w-5xl px-4 py-6 text-center text-xs text-gray-500">
            &copy; {{ now()->year }} {{ $site?->sitename ?? config('app.name') }}
        </div>
    </footer>

    @livewireScripts
</body>
</html>
