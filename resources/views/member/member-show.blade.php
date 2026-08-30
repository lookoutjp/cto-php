<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('members.index') }}" class="text-sm text-gray-500 hover:text-brand">&larr; メンバー</a>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ $member->displayName() }}</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-lg bg-white p-6 shadow-sm">

                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-lg font-medium text-gray-900">{{ $member->displayName() }}</span>
                    @if ($member->nameread)
                        <span class="text-sm text-gray-400">{{ $member->nameread }}</span>
                    @endif
                    <span class="rounded bg-brand-bg px-2 py-0.5 text-xs text-brand">{{ $roleLabel }}</span>
                    @if ($member->online)
                        <span class="rounded bg-green-100 px-1.5 py-0.5 text-[10px] text-green-700">オンライン</span>
                    @endif
                </div>

                <dl class="mt-4 space-y-3 text-sm">
                    @if ($member->appeal)
                        <div class="flex gap-3">
                            <dt class="w-24 shrink-0 text-gray-500">ニックネーム</dt>
                            <dd class="text-gray-900">{{ $member->appeal }}</dd>
                        </div>
                    @endif
                    @if ($member->sexLabel())
                        <div class="flex gap-3">
                            <dt class="w-24 shrink-0 text-gray-500">性別</dt>
                            <dd class="text-gray-900">{{ $member->sexLabel() }}</dd>
                        </div>
                    @endif
                    @if ($member->homepageUrl())
                        <div class="flex gap-3">
                            <dt class="w-24 shrink-0 text-gray-500">ホームページ</dt>
                            <dd>
                                <a href="{{ $member->homepageUrl() }}" target="_blank" rel="noopener nofollow"
                                   class="text-blue-600 hover:underline">{{ $member->hp }}</a>
                            </dd>
                        </div>
                    @endif
                </dl>

                @if (filled($member->introduce))
                    <div class="mt-5 border-t border-gray-100 pt-4">
                        <div class="mb-1 text-xs text-gray-500">自己紹介</div>
                        <p class="text-sm leading-relaxed text-gray-700">{!! nl2br(e(trim(strip_tags($member->introduce)))) !!}</p>
                    </div>
                @endif

                <div class="mt-6 border-t border-gray-100 pt-4">
                    <a href="{{ route('messages.create', ['to' => $member->member_id]) }}"
                       class="text-sm text-brand hover:underline">この人にメッセージを送る</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
