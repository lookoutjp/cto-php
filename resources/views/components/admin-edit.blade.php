@props([
    'href',
    'label' => '編集',
    'showLabel' => false,
    'icon' => 'pencil',
])
{{-- 管理者モードのインライン管理リンク（→ Filament の編集/作成ページ、保存後は元の画面へ戻る）。 --}}
@php($sep = \Illuminate\Support\Str::contains($href, '?') ? '&' : '?')
<a href="{{ $href }}{{ $sep }}back={{ urlencode(url()->full()) }}"
   {{ $attributes->merge(['class' => 'inline-flex shrink-0 items-center gap-1 rounded-md border border-amber-500 bg-amber-50 px-1.5 py-1 text-xs font-medium text-amber-700 transition hover:bg-amber-100']) }}
   title="{{ $label }}">
    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        @if ($icon === 'plus')
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        @else
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
        @endif
    </svg>
    @if ($showLabel)
        <span>{{ $label }}</span>
    @else
        <span class="sr-only">{{ $label }}</span>
    @endif
</a>
