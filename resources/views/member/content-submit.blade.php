<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            <a href="{{ route('contents.index', ['category' => $category->id]) }}" class="text-gray-500 hover:underline">{{ $category->name }}</a>
            <span class="text-gray-400">/</span> {{ $editing ? '投稿を編集' : '投稿する' }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
            @endif

            @unless ($canPublishDirectly)
                <p class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    投稿はカテゴリ管理員の承認後に公開されます。承認までの状況は
                    <a href="{{ route('contents.mine') }}" class="font-medium underline">投稿の管理</a> で確認できます。
                </p>
            @endunless

            <form method="post" action="{{ route('contents.submit', $category->id) }}"
                  class="space-y-4 rounded-lg bg-white p-6 shadow-sm">
                @csrf
                @if ($editing)
                    <input type="hidden" name="edit" value="{{ $editing->id }}">
                @endif

                <div>
                    <label class="block text-xs font-medium text-gray-600">タイトル</label>
                    <input type="text" name="name" required maxlength="200"
                           value="{{ old('name', $editing->name ?? '') }}"
                           class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand focus:ring-brand">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600">本文</label>
                    <div class="mt-1">
                        <x-rich-text name="explain" :value="old('explain', $editing->explain ?? '')" min-height="16rem" />
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('contents.index', ['category' => $category->id]) }}" class="text-sm text-gray-500 hover:underline">キャンセル</a>
                    <button type="submit" class="rounded-lg bg-brand px-5 py-2 text-sm font-medium text-brand-fg hover:bg-brand-dark">
                        {{ $canPublishDirectly ? '公開する' : ($editing ? '再申請する' : '投稿する') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
