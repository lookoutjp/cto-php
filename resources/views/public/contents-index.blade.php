<x-layouts.public title="コンテンツ">
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
</x-layouts.public>
