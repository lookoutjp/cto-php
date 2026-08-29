<x-layouts.public :title="$news->title">
    <article class="rounded-lg border border-gray-200 bg-white p-6">
        <div class="mb-4 flex items-baseline gap-3 border-b border-gray-100 pb-4">
            <time class="text-sm tabular-nums text-gray-500">
                {{ optional($news->newsdate)->isoFormat('YYYY年M月D日') }}
            </time>
            @if ($news->isPinned())
                <span class="rounded bg-red-50 px-1.5 py-0.5 text-xs font-medium text-red-600">重要</span>
            @endif
        </div>

        <div class="prose prose-sm max-w-none prose-headings:font-semibold prose-a:text-blue-600">
            {!! $news->content !!}
        </div>
    </article>

    <nav class="mt-6 flex items-center justify-between text-sm">
        <span>
            @if ($prev)
                <a href="{{ route('news.show', $prev) }}" class="text-gray-600 hover:text-gray-900 hover:underline">&larr; {{ \Illuminate\Support\Str::limit($prev->title, 24) }}</a>
            @endif
        </span>
        <a href="{{ route('news.index') }}" class="text-gray-600 hover:text-gray-900 hover:underline">一覧へ</a>
        <span>
            @if ($next)
                <a href="{{ route('news.show', $next) }}" class="text-gray-600 hover:text-gray-900 hover:underline">{{ \Illuminate\Support\Str::limit($next->title, 24) }} &rarr;</a>
            @endif
        </span>
    </nav>
</x-layouts.public>
