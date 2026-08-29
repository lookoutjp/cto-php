<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">マイページ</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="rounded-lg bg-white p-6 shadow-sm">
                <p class="text-sm text-gray-500">こんにちは</p>
                <p class="text-lg font-semibold text-gray-900">{{ $member->name ?: $member->getKey() }} さん</p>
            </div>

            <div class="rounded-lg bg-white p-6 shadow-sm">
                <p class="text-sm text-gray-600">
                    現在このサイトのプロジェクト機能（TODO・課題・リスク・WBS・サーベイ）はご利用いただけません。
                    ご利用にはサイト管理者による権限設定が必要です。
                </p>
                <div class="mt-4 flex gap-3 text-sm">
                    <a href="{{ route('home') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-50">サイトを見る</a>
                    <a href="{{ route('profile.edit') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-50">プロフィール</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
