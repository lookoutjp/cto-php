@php
    $money = fn (int $yen) => '¥' . number_format($yen);
@endphp

<x-filament-panels::page>
    <div class="space-y-6">

        {{-- 状態バナー --}}
        @if (! $stripeConfigured)
            <x-filament::section>
                <div class="text-sm text-warning-600 dark:text-warning-400">
                    <strong>Stripe が未設定です。</strong>
                    現在は使用状況の確認のみ可能です。契約・プラン変更を有効にするには
                    <code>STRIPE_SECRET</code> と各プランの Price ID（<code>STRIPE_PRICE_STANDARD</code> 等）を設定してください。
                </div>
            </x-filament::section>
        @endif

        @if ($delinquent)
            <x-filament::section>
                <div class="text-sm text-danger-600 dark:text-danger-400">
                    <strong>お支払いが確認できていません。</strong>
                    お支払い方法を更新してください。未解決のあいだ、データの新規作成・編集が制限されます。
                </div>
            </x-filament::section>
        @endif

        {{-- 現在のプラン --}}
        <x-filament::section>
            <x-slot name="heading">現在のプラン</x-slot>

            <div class="flex flex-wrap items-center gap-x-8 gap-y-3">
                <div>
                    <div class="text-2xl font-bold">{{ $currentPlan['name'] }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $currentPlan['price'] > 0 ? $money($currentPlan['price']) . ' / 月' : '無料' }}
                    </div>
                </div>

                @if ($subscription)
                    <div class="text-sm">
                        <div>
                            状態:
                            <span class="font-medium">{{ $subscription->stripe_status }}</span>
                        </div>
                        @if ($onGracePeriod)
                            <div class="text-warning-600 dark:text-warning-400">
                                解約済み — {{ $subscription->ends_at?->isoFormat('YYYY年M月D日') }} まで利用可
                            </div>
                        @elseif ($renewsAt)
                            <div class="text-gray-500 dark:text-gray-400">
                                次回請求日: {{ $renewsAt->isoFormat('YYYY年M月D日') }}
                            </div>
                        @endif
                        @if ($room->pm_last_four)
                            <div class="text-gray-500 dark:text-gray-400">
                                カード: {{ $room->pm_type }} •••• {{ $room->pm_last_four }}
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            {{-- 使用状況 --}}
            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                    <div class="text-sm text-gray-500 dark:text-gray-400">会員数</div>
                    <div class="mt-1 text-lg font-semibold">
                        {{ $memberUsage }}
                        <span class="text-sm font-normal text-gray-500">
                            / {{ $memberLimit === null ? '無制限' : $memberLimit }}
                        </span>
                    </div>
                    @if ($memberLimit !== null && $memberUsage > $memberLimit)
                        <div class="mt-1 text-xs text-danger-600 dark:text-danger-400">
                            上限を超えています。新しい会員を追加できません。
                        </div>
                    @endif
                </div>
                <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                    <div class="text-sm text-gray-500 dark:text-gray-400">ストレージ</div>
                    <div class="mt-1 text-lg font-semibold">
                        {{ \App\Support\FileStorage::humanSize($storageUsedBytes) }}
                        <span class="text-sm font-normal text-gray-500">
                            / {{ $storageLimit === null ? '無制限' : number_format($storageLimit) . ' MB' }}
                        </span>
                    </div>
                    @if ($storageLimit !== null && $storageUsedBytes > $storageLimit * 1024 * 1024)
                        <div class="mt-1 text-xs text-danger-600 dark:text-danger-400">
                            上限を超えています。新しいファイルをアップロードできません。
                        </div>
                    @endif
                </div>
            </div>

            @if ($subscription)
                <x-slot name="footerActions">
                    @if ($onGracePeriod)
                        <x-filament::button wire:click="resume" color="primary">
                            契約を再開する
                        </x-filament::button>
                    @else
                        <x-filament::button wire:click="billingPortal" color="gray" icon="heroicon-o-credit-card">
                            お支払い方法の更新
                        </x-filament::button>
                        <x-filament::button
                            wire:click="cancel"
                            color="danger"
                            wire:confirm="このサイトの契約を解約します。よろしいですか？（請求期間の終わりまでは利用できます）"
                        >
                            解約する
                        </x-filament::button>
                    @endif
                </x-slot>
            @endif
        </x-filament::section>

        {{-- プラン一覧 --}}
        <x-filament::section>
            <x-slot name="heading">プラン</x-slot>

            <div class="grid gap-4 md:grid-cols-3">
                @foreach ($plans as $key => $plan)
                    @php
                        $isCurrent = $key === $currentPlan['key'];
                        $canBuy = $stripeConfigured && ! empty($plan['stripe_price_id']);
                    @endphp
                    <div @class([
                        'rounded-xl border p-5 flex flex-col',
                        'border-primary-500 ring-1 ring-primary-500' => $isCurrent,
                        'border-gray-200 dark:border-gray-700' => ! $isCurrent,
                    ])>
                        <div class="text-lg font-bold">{{ $plan['name'] }}</div>
                        <div class="mt-1 text-2xl font-bold">
                            {{ $plan['price'] > 0 ? $money($plan['price']) : '¥0' }}
                            <span class="text-sm font-normal text-gray-500">/ 月</span>
                        </div>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $plan['description'] }}</p>

                        <ul class="mt-3 space-y-1 text-sm text-gray-600 dark:text-gray-300">
                            <li>会員 {{ $plan['limits']['members'] === null ? '無制限' : $plan['limits']['members'] . ' 名' }}</li>
                            <li>ストレージ {{ $plan['limits']['storage_mb'] === null ? '無制限' : number_format($plan['limits']['storage_mb']) . ' MB' }}</li>
                        </ul>

                        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                            @if ($isCurrent)
                                <span class="text-sm font-medium text-primary-600 dark:text-primary-400">利用中</span>
                            @elseif ($key === 'free')
                                @if ($subscription && ! $onGracePeriod)
                                    <x-filament::button wire:click="cancel" color="gray" size="sm"
                                        wire:confirm="フリープランに戻します（解約）。よろしいですか？">
                                        フリーに戻す
                                    </x-filament::button>
                                @else
                                    <span class="text-sm text-gray-400">—</span>
                                @endif
                            @elseif (! $canBuy)
                                <x-filament::button color="gray" size="sm" disabled>
                                    選択不可（Stripe未設定）
                                </x-filament::button>
                            @elseif ($subscription && ! $onGracePeriod)
                                <x-filament::button wire:click="swap('{{ $key }}')" color="primary" size="sm"
                                    wire:confirm="プランを「{{ $plan['name'] }}」に変更します。差額は日割りで調整されます。">
                                    このプランに変更
                                </x-filament::button>
                            @else
                                <x-filament::button wire:click="subscribe('{{ $key }}')" color="primary" size="sm">
                                    このプランを契約
                                </x-filament::button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </x-filament::section>

    </div>
</x-filament-panels::page>
