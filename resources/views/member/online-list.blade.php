<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">オンラインメンバー</h2>
    </x-slot>

    <div class="py-8">
        <div class="px-4 sm:px-6 lg:px-8">
            <p class="mb-3 text-sm text-gray-500">
                直近 {{ \App\Models\Member::PRESENCE_MINUTES }} 分以内にアクセスした参加者（{{ $members->count() }} 名）
            </p>

            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                @forelse ($members as $m)
                    <div class="flex items-center gap-3 border-b border-gray-100 px-5 py-3 last:border-0">
                        <span class="h-2 w-2 shrink-0 rounded-full bg-green-500"></span>
                        <div class="min-w-0 flex-1">
                            <a href="{{ route('members.show', $m) }}" class="font-medium text-gray-900 hover:text-brand hover:underline">{{ $m->name ?: $m->member_id }}</a>
                            @if ($m->nameread)
                                <span class="ml-2 text-xs text-gray-400">{{ $m->nameread }}</span>
                            @endif
                        </div>
                        <span class="shrink-0 text-xs text-gray-400">{{ optional($m->timerenew)->isoFormat('H:mm') }}</span>
                        <a href="{{ route('messages.create', ['to' => $m->member_id]) }}"
                           class="shrink-0 rounded-md border border-gray-200 px-2.5 py-1 text-xs text-gray-700 hover:bg-gray-50">メッセージ</a>
                    </div>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-gray-400">現在オンラインの参加者はいません。</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
