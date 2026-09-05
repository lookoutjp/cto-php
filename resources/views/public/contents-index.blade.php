@php
    $breadcrumb = match ($mode) {
        'category' => $category->name,
        'search' => "「{$keyword}」の検索結果",
        default => 'コンテンツ',
    };
@endphp

<x-layouts.public title="コンテンツ">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm">
        @if ($mode === 'category')
            {{-- トップ→親カテゴリ→…→本カテゴリの階層を明示する（旧ASP同様）。 --}}
            <div class="text-gray-500">
                現在位置：<a href="{{ route('contents.index') }}" class="hover:text-brand hover:underline">トップ</a>
                @foreach ($ancestors as $ancestor)
                    <span class="mx-1 text-gray-400">→</span>
                    <a href="{{ route('contents.index', ['category' => $ancestor->id]) }}" class="hover:text-brand hover:underline">{{ $ancestor->name }}</a>
                @endforeach
                <span class="mx-1 text-gray-400">→</span>
                <span class="font-medium text-gray-800">{{ $category->name }}</span>
            </div>
        @else
            <div class="text-gray-500">現在位置：<span class="font-medium text-gray-800">{{ $breadcrumb }}</span></div>
        @endif
        <form method="get" action="{{ route('contents.index') }}" class="flex items-center gap-2">
            <input type="text" name="q" value="{{ $keyword ?? '' }}" placeholder="キーワード検索"
                   class="w-40 rounded-md border-gray-300 text-sm shadow-sm focus:border-brand focus:ring-brand sm:w-56">
            <button type="submit" class="rounded-md bg-brand px-3 py-1.5 text-sm font-medium text-brand-fg hover:bg-brand-dark">検索</button>
        </form>
    </div>

    {{-- 旧ASPのカテゴリ詳細画面（contents.asp?Contentsort=N）は「現在位置」の直下に
         カテゴリの紹介文（content_sorts.introduce）を表示していた。それに合わせる。 --}}
    @if ($mode === 'category' && filled($category->introduce))
        <div class="prose prose-sm mb-4 max-w-none rounded-lg border border-gray-200 bg-white px-4 py-4 text-gray-700">
            {!! $category->introduce !!}
        </div>
    @endif

    @if ($mode === 'search')
        @if ($results->isEmpty())
            <p class="rounded-lg border border-gray-200 bg-white px-4 py-8 text-center text-sm text-gray-500">
                「{{ $keyword }}」に一致するコンテンツは見つかりませんでした。
            </p>
        @else
            <ul class="divide-y divide-gray-100 overflow-hidden rounded-lg border border-gray-200 bg-white">
                @foreach ($results as $content)
                    <li class="px-4 py-3">
                        <a href="{{ route('contents.show', $content) }}" class="font-medium text-gray-900 hover:text-brand hover:underline">{{ $content->name }}</a>
                        @if ($content->title2)
                            <p class="mt-0.5 truncate text-sm text-gray-500">{{ \Illuminate\Support\Str::limit(strip_tags($content->title2), 80) }}</p>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    @elseif ($mode === 'category')
        <div class="space-y-6">
            @if ($ownContents->isNotEmpty())
                <ul class="divide-y divide-gray-100 overflow-hidden rounded-lg border border-gray-200 bg-white">
                    @foreach ($ownContents as $content)
                        <li class="px-4 py-3">
                            <a href="{{ route('contents.show', $content) }}" class="font-medium text-gray-900 hover:text-brand hover:underline">{{ $content->name }}</a>
                            @if ($content->title2)
                                <p class="mt-0.5 truncate text-sm text-gray-500">{{ \Illuminate\Support\Str::limit(strip_tags($content->title2), 80) }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif

            @foreach ($children as $child)
                <section class="overflow-hidden rounded-lg border border-gray-200 bg-white">
                    <a href="{{ route('contents.index', ['category' => $child->id]) }}"
                       class="block bg-brand px-4 py-2 text-sm font-semibold text-brand-fg hover:bg-brand-dark">
                        {{ $child->name }}
                    </a>
                    @if ($child->contents->isEmpty())
                        <p class="px-4 py-4 text-sm text-gray-400">コンテンツはまだありません。</p>
                    @else
                        <ul class="divide-y divide-gray-100">
                            @foreach ($child->contents as $content)
                                <li class="px-4 py-3">
                                    <a href="{{ route('contents.show', $content) }}" class="font-medium text-gray-900 hover:text-brand hover:underline">{{ $content->name }}</a>
                                    @if ($content->title2)
                                        <p class="mt-0.5 truncate text-sm text-gray-500">{{ \Illuminate\Support\Str::limit(strip_tags($content->title2), 80) }}</p>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            @endforeach

            @if ($ownContents->isEmpty() && $children->isEmpty())
                <p class="rounded-lg border border-gray-200 bg-white px-4 py-8 text-center text-sm text-gray-500">
                    このカテゴリに公開中のコンテンツはありません。
                </p>
            @endif
        </div>
    @else
        @if ($categories->isEmpty())
            <p class="rounded-lg border border-gray-200 bg-white px-4 py-8 text-center text-sm text-gray-500">
                公開中のコンテンツはありません。
            </p>
        @else
            <div class="space-y-10">
                @foreach ($categories as $category)
                    @include('public.partials.category-node', ['category' => $category, 'depth' => 0])
                @endforeach
            </div>
        @endif
    @endif
</x-layouts.public>
