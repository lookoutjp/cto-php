@props(['href'])
{{-- 管理者モードの「＋追加」リンク（→ Filament の作成ページ、保存後は元の画面へ戻る）。 --}}
@php($sep = \Illuminate\Support\Str::contains($href, '?') ? '&' : '?')
<a href="{{ $href }}{{ $sep }}back={{ urlencode(url()->full()) }}"
   {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 rounded-md border border-dashed border-amber-600 px-3 py-2 text-sm font-medium text-amber-700 transition hover:bg-amber-50']) }}>
    <span aria-hidden="true">＋</span>
    {{ $slot }}
</a>
