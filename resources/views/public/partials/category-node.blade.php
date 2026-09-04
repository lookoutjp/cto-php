{{-- 再帰: コンテンツのカテゴリ階層 1 ノード --}}
<section @class(['border-l-2 border-gray-100 pl-4' => $depth > 0])>
    <h2 @class([
        'font-semibold text-gray-900',
        'text-lg mb-3' => $depth === 0,
        'text-base mt-4 mb-2' => $depth === 1,
        'text-sm mt-3 mb-2 text-gray-700' => $depth >= 2,
    ])>
        <a href="{{ route('contents.index', ['category' => $category->id]) }}" class="hover:text-brand hover:underline">
            {{ $category->name }}
        </a>
        @if ($category->contents->isNotEmpty())
            <span class="ml-1 text-xs font-normal text-gray-400">{{ $category->contents->count() }}</span>
        @endif
    </h2>

    @if ($category->contents->isNotEmpty())
        <ul class="divide-y divide-gray-100 overflow-hidden rounded-lg border border-gray-200 bg-white">
            @foreach ($category->contents as $content)
                <li class="px-4 py-3">
                    <a href="{{ route('contents.show', $content) }}"
                       class="font-medium text-gray-900 hover:text-brand hover:underline">
                        {{ $content->name }}
                    </a>
                    @if ($content->title2)
                        <p class="mt-0.5 text-sm text-gray-500">{{ \Illuminate\Support\Str::limit(strip_tags($content->title2), 80) }}</p>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif

    @if ($category->kids->isNotEmpty())
        <div @class(['space-y-4', 'mt-4' => $category->contents->isNotEmpty()])>
            @foreach ($category->kids as $child)
                @include('public.partials.category-node', ['category' => $child, 'depth' => $depth + 1])
            @endforeach
        </div>
    @endif
</section>
