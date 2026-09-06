<x-layouts.public title="リンク集">
    @if ($adminMode ?? false)
        <div class="mb-4">
            <x-admin-add :href="route('filament.admin.resources.link-items.create')">リンクを追加</x-admin-add>
        </div>
    @endif

    @if ($links->isEmpty())
        <p class="rounded-lg border border-gray-200 bg-white px-4 py-8 text-center text-sm text-gray-500">
            リンクはまだ登録されていません。
        </p>
    @else
        <ul class="grid gap-4 sm:grid-cols-2">
            @foreach ($links as $link)
                @php($pending = ! in_array($link->allow, [1, '1'], true))
                <li @class([
                    'rounded-lg border bg-white p-4',
                    'border-gray-200' => ! $pending,
                    'border-dashed border-amber-400 bg-amber-50/40' => $pending,
                ])>
                    <div class="flex items-start justify-between gap-2">
                        <a href="{{ \Illuminate\Support\Str::startsWith($link->homepage, ['http://', 'https://']) ? $link->homepage : '//'.$link->homepage }}"
                           target="_blank" rel="noopener nofollow"
                           class="font-medium text-blue-600 hover:underline">{{ $link->name }}</a>
                        @if ($adminMode ?? false)
                            <span class="flex shrink-0 items-center gap-1">
                                @if ($pending)
                                    <span class="rounded bg-amber-200 px-1.5 py-0.5 text-xs font-medium text-amber-800">承認待ち</span>
                                @endif
                                <x-admin-edit :href="route('filament.admin.resources.link-items.edit', $link)"
                                              :label="'「'.$link->name.'」を編集'" />
                            </span>
                        @endif
                    </div>
                    @if ($link->com)
                        <p class="mt-1 text-sm text-gray-600">{{ \Illuminate\Support\Str::limit(strip_tags($link->com), 120) }}</p>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
</x-layouts.public>
