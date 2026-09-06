<div>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        @if ($adminMode ?? false)
            <x-admin-add :href="route('filament.admin.resources.news-items.create')">ニュースを追加</x-admin-add>
        @else
            <span></span>
        @endif

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
                    <div class="mt-1 flex items-start justify-between gap-2">
                        <a href="{{ route('news.show', $item) }}"
                           class="block font-medium text-gray-900 hover:text-gray-600 hover:underline">
                            {{ $item->title }}
                        </a>
                        @if ($adminMode ?? false)
                            <x-admin-edit :href="route('filament.admin.resources.news-items.edit', $item)"
                                          :label="'「'.\Illuminate\Support\Str::limit($item->title, 20).'」を編集'" />
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>

        <div class="mt-6">
            {{ $news->links() }}
        </div>
    @endif
</div>
