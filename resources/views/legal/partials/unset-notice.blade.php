@php
    $missing = collect(['operator', 'representative', 'address', 'phone', 'email'])
        ->filter(fn ($k) => blank(data_get($legal, $k)))
        ->values();
@endphp

@if ($missing->isNotEmpty() && auth()->user()?->isSuperAdmin())
    <div class="mb-6 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800">
        <p class="font-semibold">運営者情報が未設定です（この警告はスーパー管理者にのみ表示されます）</p>
        <p class="mt-1">未設定: {{ $missing->implode(', ') }}。<code>config/legal.php</code> に対応する <code>LEGAL_*</code> 環境変数を設定してください。</p>
    </div>
@endif
