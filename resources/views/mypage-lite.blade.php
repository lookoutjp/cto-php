<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">マイページ</h2>
    </x-slot>

    <div class="py-8">
        <div class="space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('status') }}</div>
            @endif
            <div class="rounded-lg bg-white p-6 shadow-sm">
                <p class="text-sm text-gray-500">こんにちは</p>
                <p class="text-lg font-semibold text-gray-900">{{ $member->name ?: $member->getKey() }} さん</p>
            </div>

            @if ($pendingApproval ?? false)
                <div class="rounded-lg border border-amber-300 bg-amber-50 p-6 shadow-sm">
                    <p class="font-semibold text-amber-800">このサイトへの加入を申請中です（承認待ち）</p>
                    <p class="mt-1 text-sm text-amber-700">
                        サイト管理員の承認をお待ちください。承認されると各機能をご利用いただけます。
                        承認までの間も公開コンテンツはご覧いただけます。
                    </p>
                </div>
            @endif

            <div class="rounded-lg bg-white p-6 shadow-sm">
                <p class="text-sm text-gray-600">
                    現在このサイトのプロジェクト機能（TODO・課題・リスク・WBS・サーベイ）はご利用いただけません。
                    ご利用にはサイト管理者による権限設定が必要です。
                </p>
                <div class="mt-4 flex flex-wrap gap-3 text-sm">
                    <a href="{{ route('home') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-50">サイトを見る</a>
                    <a href="{{ route('profile.edit') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-50">プロフィール</a>
                    <a href="{{ route('site-join.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-50">サイトへの加入</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
