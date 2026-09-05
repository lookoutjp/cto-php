<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            <a href="{{ route('board.index') }}" class="text-gray-500 hover:underline">掲示板</a>
            <span class="text-gray-400">/</span>
            <a href="{{ route('board.category', $cat->id) }}" class="text-gray-500 hover:underline">{!! $cat->displayName() !!}</a>
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="space-y-4 px-4 sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
            @endif

            <article class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h1 class="text-lg font-bold text-gray-900">{{ $post->title }}</h1>
                <div class="mt-1 flex items-baseline gap-3 text-xs text-gray-500">
                    <span class="font-medium text-gray-700">{{ $post->author?->name ?? $post->user_name }}</span>
                    <time class="tabular-nums">{{ optional($post->create_date)->isoFormat('YYYY年M月D日 HH:mm') }}</time>
                </div>
                <div class="prose prose-sm mt-3 max-w-none">{!! $post->content !!}</div>

                @if ($post->hasManagerReply())
                    <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-3">
                        <p class="text-xs font-semibold text-amber-700">管理員返信</p>
                        <div class="prose prose-sm mt-1 max-w-none text-amber-900">{!! $post->revert !!}</div>
                    </div>
                @endif

                @include('member.partials.board-reply-form', ['parentId' => $post->id, 'label' => 'このスレッドに返信'])
            </article>

            <div class="space-y-3">
                @forelse ($replies as $node)
                    @include('member.partials.board-reply-node', ['node' => $node])
                @empty
                    <p class="text-center text-sm text-gray-400">返信はまだありません。</p>
                @endforelse
            </div>

            <div class="text-sm">
                <a href="{{ route('board.category', $cat->id) }}" class="text-gray-600 hover:text-gray-900 hover:underline">&larr; 一覧へ</a>
            </div>
        </div>
    </div>
</x-app-layout>
