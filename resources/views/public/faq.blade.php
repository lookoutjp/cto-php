<x-layouts.public title="FAQ">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        @if ($adminMode ?? false)
            <x-admin-add :href="route('filament.admin.resources.faqs.create')">FAQを追加</x-admin-add>
        @else
            <span></span>
        @endif

        <form method="get" class="flex justify-end">
            <input
                type="search"
                name="q"
                value="{{ $keyword }}"
                placeholder="キーワードで検索"
                class="w-full max-w-xs rounded-lg border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500"
            >
        </form>
    </div>

    @if ($faqs->isEmpty())
        <p class="rounded-lg border border-gray-200 bg-white px-4 py-8 text-center text-sm text-gray-500">
            該当するFAQはありません。
        </p>
    @else
        <div class="divide-y divide-gray-100 overflow-hidden rounded-lg border border-gray-200 bg-white">
            @foreach ($faqs as $faq)
                <details class="group px-4 py-3" @if ($keyword !== '') open @endif>
                    <summary class="flex cursor-pointer list-none items-start gap-2 font-medium text-gray-900">
                        <span class="mt-0.5 select-none text-gray-400 transition group-open:rotate-90">&rsaquo;</span>
                        <span class="flex-1">{{ $faq->question }}</span>
                        @if ($adminMode ?? false)
                            <x-admin-edit :href="route('filament.admin.resources.faqs.edit', $faq)"
                                          :label="'「'.\Illuminate\Support\Str::limit($faq->question, 20).'」を編集'" />
                        @endif
                    </summary>
                    <div class="prose prose-sm mt-3 max-w-none pl-6 text-gray-700">
                        {!! $faq->answer !!}
                    </div>
                </details>
            @endforeach
        </div>
    @endif
</x-layouts.public>
