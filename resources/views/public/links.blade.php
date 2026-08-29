<x-layouts.public title="リンク集">
    @if ($links->isEmpty())
        <p class="rounded-lg border border-gray-200 bg-white px-4 py-8 text-center text-sm text-gray-500">
            リンクはまだ登録されていません。
        </p>
    @else
        <ul class="grid gap-4 sm:grid-cols-2">
            @foreach ($links as $link)
                <li class="rounded-lg border border-gray-200 bg-white p-4">
                    <a href="{{ \Illuminate\Support\Str::startsWith($link->homepage, ['http://', 'https://']) ? $link->homepage : '//'.$link->homepage }}"
                       target="_blank" rel="noopener nofollow"
                       class="font-medium text-blue-600 hover:underline">{{ $link->name }}</a>
                    @if ($link->com)
                        <p class="mt-1 text-sm text-gray-600">{{ \Illuminate\Support\Str::limit(strip_tags($link->com), 120) }}</p>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
</x-layouts.public>
