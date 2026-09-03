@php
    $v = fn ($key, $fallback = '—（未設定）') => filled(data_get($legal, $key)) ? data_get($legal, $key) : $fallback;
    $yen = fn ($n) => '¥'.number_format((int) $n);
@endphp

<x-layouts.public title="特定商取引法に基づく表記">
    @include('legal.partials.unset-notice')

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
        <dl class="divide-y divide-gray-100 text-sm">
            <div class="grid grid-cols-1 gap-1 px-5 py-4 sm:grid-cols-3 sm:gap-4">
                <dt class="font-medium text-gray-500">事業者名</dt>
                <dd class="sm:col-span-2">{{ $v('operator') }}</dd>
            </div>
            <div class="grid grid-cols-1 gap-1 px-5 py-4 sm:grid-cols-3 sm:gap-4">
                <dt class="font-medium text-gray-500">運営統括責任者</dt>
                <dd class="sm:col-span-2">{{ $v('representative') }}</dd>
            </div>
            <div class="grid grid-cols-1 gap-1 px-5 py-4 sm:grid-cols-3 sm:gap-4">
                <dt class="font-medium text-gray-500">所在地</dt>
                <dd class="sm:col-span-2">{{ $v('address') }}</dd>
            </div>
            <div class="grid grid-cols-1 gap-1 px-5 py-4 sm:grid-cols-3 sm:gap-4">
                <dt class="font-medium text-gray-500">電話番号</dt>
                <dd class="sm:col-span-2">
                    {{ $v('phone') }}
                    @if (filled(data_get($legal, 'phone_hours')))
                        <span class="text-gray-400">（受付時間: {{ $legal['phone_hours'] }}）</span>
                    @endif
                </dd>
            </div>
            <div class="grid grid-cols-1 gap-1 px-5 py-4 sm:grid-cols-3 sm:gap-4">
                <dt class="font-medium text-gray-500">メールアドレス</dt>
                <dd class="sm:col-span-2">{{ $v('email') }}</dd>
            </div>
            <div class="grid grid-cols-1 gap-1 px-5 py-4 sm:grid-cols-3 sm:gap-4">
                <dt class="font-medium text-gray-500">販売価格</dt>
                <dd class="sm:col-span-2">
                    <ul class="space-y-0.5">
                        @foreach ($plans as $key => $plan)
                            <li>
                                {{ $plan['name'] }}:
                                @if ((int) $plan['price'] === 0)
                                    無料
                                @else
                                    {{ $yen($plan['price']) }} / 月（税込）
                                @endif
                            </li>
                        @endforeach
                    </ul>
                    <p class="mt-1 text-gray-400">各プランの詳細・上限は申込画面（管理画面 &gt; お支払い）に表示されます。</p>
                </dd>
            </div>
            <div class="grid grid-cols-1 gap-1 px-5 py-4 sm:grid-cols-3 sm:gap-4">
                <dt class="font-medium text-gray-500">商品代金以外の必要料金</dt>
                <dd class="sm:col-span-2">{{ $v('extra_fees', 'なし') }}</dd>
            </div>
            <div class="grid grid-cols-1 gap-1 px-5 py-4 sm:grid-cols-3 sm:gap-4">
                <dt class="font-medium text-gray-500">支払方法</dt>
                <dd class="sm:col-span-2">クレジットカード（決済代行: Stripe）</dd>
            </div>
            <div class="grid grid-cols-1 gap-1 px-5 py-4 sm:grid-cols-3 sm:gap-4">
                <dt class="font-medium text-gray-500">支払時期</dt>
                <dd class="sm:col-span-2">お申し込み時に初回分を決済し、以後は毎月同日に自動更新・自動決済します。</dd>
            </div>
            <div class="grid grid-cols-1 gap-1 px-5 py-4 sm:grid-cols-3 sm:gap-4">
                <dt class="font-medium text-gray-500">サービス提供時期</dt>
                <dd class="sm:col-span-2">決済完了後、ただちにご利用いただけます。</dd>
            </div>
            <div class="grid grid-cols-1 gap-1 px-5 py-4 sm:grid-cols-3 sm:gap-4">
                <dt class="font-medium text-gray-500">解約・返金</dt>
                <dd class="sm:col-span-2">
                    管理画面の「お支払い」からいつでも解約できます。解約後は当該請求期間の終了までご利用いただけます。
                    サービスの性質上、日割りでの返金は行いません。
                </dd>
            </div>
            <div class="grid grid-cols-1 gap-1 px-5 py-4 sm:grid-cols-3 sm:gap-4">
                <dt class="font-medium text-gray-500">動作環境</dt>
                <dd class="sm:col-span-2">最新版の Google Chrome / Microsoft Edge / Safari / Firefox を推奨します。</dd>
            </div>
        </dl>
    </div>

    <p class="mt-6 text-xs text-gray-400">最終改定日: {{ data_get($legal, 'tokushoho_updated') }}</p>

    @include('legal.partials.footer-links')
</x-layouts.public>
