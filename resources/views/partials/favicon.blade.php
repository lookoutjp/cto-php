@php
    $favicon = trim((string) ($site?->favicon ?? ''));
    $faviconUrl = $favicon !== ''
        ? (\Illuminate\Support\Str::startsWith($favicon, ['http://', 'https://']) ? $favicon : asset(ltrim($favicon, '/')))
        : asset('img/favicon.png');
@endphp
<link rel="icon" href="{{ $faviconUrl }}">
