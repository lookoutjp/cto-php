<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">メンバー</h2>
    </x-slot>

    <div class="py-8">
        <div class="space-y-3 px-4 sm:px-6 lg:px-8">
            @if ($site?->hasFunction('onlinemembersfunction'))
                <div class="text-sm">
                    <a href="{{ route('members.online') }}" class="text-brand hover:underline">オンラインメンバーを見る →</a>
                </div>
            @endif
            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                @forelse ($members as $m)
                    <div class="flex items-start gap-3 border-b border-gray-100 px-5 py-4 last:border-0">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('members.show', $m) }}" class="font-medium text-gray-900 hover:text-brand hover:underline">{{ $m->name ?: $m->member_id }}</a>
                                @if ($m->nameread)
                                    <span class="text-xs text-gray-400">{{ $m->nameread }}</span>
                                @endif
                                @if ($m->isOnline())
                                    <span class="rounded bg-green-100 px-1.5 py-0.5 text-[10px] text-green-700">オンライン</span>
                                @endif
                            </div>
                            @if (filled($m->introduce) || filled($m->appeal))
                                <p class="mt-1 text-sm text-gray-600">{{ \Illuminate\Support\Str::limit(trim(strip_tags($m->introduce ?: $m->appeal)), 160) }}</p>
                            @endif
                        </div>
                        @if ($m->hp)
                            <a href="{{ \Illuminate\Support\Str::startsWith($m->hp, ['http://', 'https://']) ? $m->hp : '//'.$m->hp }}"
                               target="_blank" rel="noopener nofollow" class="shrink-0 text-xs text-blue-600 hover:underline">サイト</a>
                        @endif
                    </div>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-gray-400">メンバーがいません。</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
