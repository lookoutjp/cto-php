<div>
    <div class="mb-6 flex justify-end">
        <input
            type="search"
            wire:model.live.debounce.400ms="keyword"
            placeholder="タイトルで検索"
            class="w-full max-w-xs rounded-lg border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500"
        >
    </div>

    @if ($news->isEmpty())
        <p class="rounded-lg border border-gray-200 bg-white px-4 py-8 text-center text-sm text-gray-500">
            該当するニュースはありません。
        </p>
    @else
        <ul class="divide-y divide-gray-100 overflow-hidden rounded-lg border border-gray-200 bg-white">
            @foreach ($news as $item)
                <li class="px-4 py-4">
                    <div class="flex items-baseline gap-3">
                        <time class="shrink-0 text-sm tabular-nums text-gray-500">
                            {{ optional($item->newsdate)->isoFormat('YYYY年M月D日') }}
                        </time>
                        @if ($item->isPinned())
                            <span class="rounded bg-red-50 px-1.5 py-0.5 text-xs font-medium text-red-600">重要</span>
                        @endif
                    </div>
                    <a href="{{ route('news.show', $item) }}"
                       class="mt-1 block font-medium text-gray-900 hover:text-gray-600 hover:underline">
                        {{ $item->title }}
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="mt-6">
            {{ $news->links() }}
        </div>
    @endif
</div>
