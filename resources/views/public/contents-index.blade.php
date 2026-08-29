<x-layouts.public title="コンテンツ">
    @if ($categories->isEmpty())
        <p class="rounded-lg border border-gray-200 bg-white px-4 py-8 text-center text-sm text-gray-500">
            公開中のコンテンツはありません。
        </p>
    @else
        <div class="space-y-8">
            @foreach ($categories as $category)
                <section>
                    <h2 class="mb-3 text-lg font-semibold text-gray-900">{{ $category->name }}</h2>
                    <ul class="divide-y divide-gray-100 overflow-hidden rounded-lg border border-gray-200 bg-white">
                        @foreach ($category->contents as $content)
                            <li class="px-4 py-3">
                                <a href="{{ route('contents.show', $content) }}"
                                   class="font-medium text-gray-900 hover:text-gray-600 hover:underline">
                                    {{ $content->name }}
                                </a>
                                @if ($content->title2)
                                    <p class="mt-0.5 text-sm text-gray-500">{{ \Illuminate\Support\Str::limit(strip_tags($content->title2), 80) }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        </div>
    @endif
</x-layouts.public>
