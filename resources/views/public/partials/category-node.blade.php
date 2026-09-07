{{-- 再帰: コンテンツのカテゴリ階層 1 ノード --}}
<section @class(['border-l-2 border-gray-100 pl-4' => $depth > 0])>
    {{-- カテゴリ詳細ページ（/contents?category=N）の子カテゴリ見出しと同じブランド色のバー。 --}}
    <h2 @class([
        'flex items-stretch overflow-hidden rounded-lg bg-brand font-semibold text-brand-fg',
        'mb-3' => $depth === 0,
        'mt-4 mb-2' => $depth === 1,
        'mt-3 mb-2' => $depth >= 2,
    ])>
        <a href="{{ route('contents.index', ['category' => $category->id]) }}"
           @class([
               'block flex-1 px-4 hover:bg-brand-dark',
               'py-2.5 text-base' => $depth === 0,
               'py-2 text-sm' => $depth >= 1,
           ])>
            {{ $category->name }}
            @if ($category->contents->isNotEmpty())
                <span class="ml-1 text-xs font-normal opacity-80">{{ $category->contents->count() }}</span>
            @endif
        </a>
        @if ($adminMode ?? false)
            <span class="flex items-center gap-1 pr-2">
                <x-admin-edit :href="route('filament.admin.resources.content-sorts.edit', $category)"
                              label="「{{ $category->name }}」を編集" class="border-white/60 bg-white/90" />
                <x-admin-edit :href="route('filament.admin.resources.contents.create', ['content_sort' => $category->id])"
                              label="「{{ $category->name }}」に記事を追加" icon="plus" class="border-white/60 bg-white/90" />
            </span>
        @else
            <span class="flex items-center pr-2">
                @include('public.partials.category-actions', ['cat' => $category])
            </span>
        @endif
    </h2>

    @if ($category->contents->isNotEmpty())
        <ul class="divide-y divide-gray-100 overflow-hidden rounded-lg border border-gray-200 bg-white">
            @foreach ($category->contents as $content)
                <li class="flex items-start justify-between gap-2 px-4 py-3">
                    <div>
                        <a href="{{ route('contents.show', $content) }}"
                           class="font-medium text-gray-900 hover:text-brand hover:underline">
                            {{ $content->name }}
                        </a>
                        @if ($content->title2)
                            <p class="mt-0.5 text-sm text-gray-500">{{ \Illuminate\Support\Str::limit(strip_tags($content->title2), 80) }}</p>
                        @endif
                    </div>
                    @if ($adminMode ?? false)
                        <x-admin-edit :href="route('filament.admin.resources.contents.edit', $content)"
                                      :label="'「'.\Illuminate\Support\Str::limit($content->name, 20).'」を編集'" />
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
