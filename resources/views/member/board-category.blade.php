<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            <a href="{{ route('board.index') }}" class="text-gray-500 hover:underline">掲示板</a>
            <span class="text-gray-400">/</span> {!! $cat->displayName() !!}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl space-y-4 px-4 sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('status') }}</div>
            @endif

            @if ($cat->intro)
                <div class="prose prose-sm max-w-none rounded-lg border border-gray-200 bg-white p-4 text-gray-600">{!! $cat->intro !!}</div>
            @endif

            <div class="flex justify-end">
                <a href="{{ route('board.create', $cat->id) }}"
                   class="rounded-lg bg-brand px-4 py-2 text-sm font-medium text-brand-fg hover:bg-brand-dark">新規投稿</a>
            </div>

            <ul class="divide-y divide-gray-100 overflow-hidden rounded-lg border border-gray-200 bg-white">
                @forelse ($threads as $t)
                    <li class="px-4 py-4">
                        <a href="{{ route('board.show', $t->id) }}"
                           class="block font-medium text-gray-900 hover:text-gray-600 hover:underline">
                            {{ $t->title }}
                        </a>
                        <div class="mt-1 flex items-baseline gap-3 text-xs text-gray-500">
                            <span>{{ $t->author?->name ?? $t->user_name }}</span>
                            <time class="tabular-nums">{{ optional($t->create_date)->isoFormat('YYYY/MM/DD HH:mm') }}</time>
                            <span>返信 {{ $replyCounts[(string) $t->id] ?? 0 }}</span>
                            @if ($t->hasManagerReply())
                                <span class="rounded bg-amber-50 px-1.5 py-0.5 font-medium text-amber-700">管理員返信あり</span>
                            @endif
                        </div>
                        @if ($excerpt = \Illuminate\Support\Str::limit(trim(strip_tags($t->content ?? '')), 120))
                            <p class="mt-1 text-sm text-gray-500">{{ $excerpt }}</p>
                        @endif
                    </li>
                @empty
                    <li class="px-4 py-8 text-center text-sm text-gray-400">まだ投稿はありません。</li>
                @endforelse
            </ul>

            @if ($threads->hasPages())
                <div>{{ $threads->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
