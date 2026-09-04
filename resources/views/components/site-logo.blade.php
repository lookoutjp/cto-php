@props(['site' => null, 'class' => 'h-9 w-auto'])

@php
    $logo = trim((string) ($site?->logo ?? ''));
    $logoUrl = $logo !== ''
        ? (\Illuminate\Support\Str::startsWith($logo, ['http://', 'https://']) ? $logo : asset(ltrim($logo, '/')))
        : null;
@endphp

@if ($logoUrl)
    <img src="{{ $logoUrl }}" alt="{{ $site->sitename }}" {{ $attributes->merge(['class' => $class.' object-contain']) }}>
@else
    <x-application-logo {{ $attributes->merge(['class' => $class.' fill-current text-brand']) }} />
@endif
