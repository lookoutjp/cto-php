<x-layouts.public :title="$content->name">
    @if ($adminMode ?? false)
        <div class="mb-4 flex flex-wrap gap-2">
            <x-admin-edit :href="route('filament.admin.resources.contents.edit', $content)"
                          label="この記事を編集" :show-label="true" />
            @if ($content->sort)
                <x-admin-edit :href="route('filament.admin.resources.content-sorts.edit', $content->sort)"
                              label="カテゴリを編集" :show-label="true" />
            @endif
            <x-admin-add :href="route('filament.admin.resources.contents.create')">記事を追加</x-admin-add>
        </div>
    @endif

    <article class="rounded-lg border border-gray-200 bg-white p-6">
        <div class="mb-4 border-b border-gray-100 pb-4">
            @if ($content->sort)
                <a href="{{ route('contents.index') }}" class="text-xs text-gray-500 hover:underline">{{ $content->sort->name }}</a>
            @endif
            <h1 class="mt-1 text-xl font-bold text-gray-900">{{ $content->name }}</h1>
        </div>

        @if ($content->introduce)
            <p class="mb-4 text-sm text-gray-600">{{ strip_tags($content->introduce) }}</p>
        @endif

        <div class="prose prose-sm max-w-none prose-a:text-blue-600">
            {!! $content->explain !!}
        </div>
    </article>

    <div class="mt-6">
        <livewire:attachments-panel type="content" :id="$content->id" :key="'att-content-'.$content->id" />
    </div>

    @if ((int) $content->commentok === 1 && $site?->hasFunction('commentfunction'))
        <livewire:public.content-comments :content="$content" />
    @endif

    <div class="mt-6 text-sm">
        <a href="{{ route('contents.index') }}" class="text-gray-600 hover:text-gray-900 hover:underline">&larr; コンテンツ一覧へ</a>
    </div>
</x-layouts.public>
