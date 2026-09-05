@php
    $ownerLabel = trim((string) ($site?->manager_shouko ?: 'オーナー'));
    $ownerName = trim((string) $site?->webmanager);

    $company = collect([
        '会社名' => $site?->comname,
        '郵便番号' => $site?->compostcode,
        '住所' => $site?->comaddress,
        '電話番号' => $site?->comphone,
        'FAX番号' => $site?->comfax,
        'メールアドレス' => $site?->comemail,
        '担当者' => $site?->comomanager,
    ])->filter(fn ($value) => filled($value));
@endphp

<x-layouts.public title="サイト概要">
    @if ($site?->logo || $ownerName)
        <x-slot name="aside">
            <div class="rounded-lg border border-gray-200 bg-white p-5 text-center">
                @if ($site?->logo)
                    <img src="{{ \Illuminate\Support\Str::startsWith($site->logo, ['http://', 'https://']) ? $site->logo : asset(trim($site->logo, '/')) }}"
                         alt="{{ $site->sitename }}"
                         @style([
                             'height: '.$site->logoheight.'px' => $site->logoheight,
                             'width: '.$site->logowidth.'px' => $site->logowidth,
                         ])
                         class="mx-auto max-w-full">
                @endif
                @if ($ownerName)
                    <p class="mt-3 text-sm text-gray-500">{{ $ownerLabel }}：{{ $ownerName }}</p>
                @endif
            </div>
        </x-slot>
    @endif

    <div class="space-y-6">
        <section class="rounded-lg border border-gray-200 bg-white p-6">
            <h1 class="mb-4 text-xl font-bold tracking-tight text-gray-900">{{ $site?->sitename ?? config('app.name') }}</h1>

            @if (filled($site?->siteintro))
                <div class="prose prose-sm max-w-none text-gray-700">
                    {!! $site->siteintro !!}
                </div>
            @else
                <p class="text-sm text-gray-400">サイト紹介文はまだ登録されていません。</p>
            @endif
        </section>

        @if ($company->isNotEmpty())
            <section class="overflow-hidden rounded-lg border border-gray-200 bg-white">
                <h2 class="border-b border-gray-200 bg-brand-bg px-4 py-2 text-base font-semibold text-brand">運営者情報</h2>
                <dl class="divide-y divide-gray-100">
                    @foreach ($company as $label => $value)
                        <div class="flex flex-col gap-1 px-4 py-3 sm:flex-row sm:gap-4">
                            <dt class="w-32 shrink-0 text-sm font-medium text-gray-500">{{ $label }}</dt>
                            <dd class="text-sm text-gray-900">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>
        @endif
    </div>
</x-layouts.public>
