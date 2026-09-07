<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            <a href="{{ route('contents.index', ['category' => $parent->id]) }}" class="text-gray-500 hover:underline">{{ $parent->name }}</a>
            <span class="text-gray-400">/</span> サブカテゴリを追加
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
            @endif

            <form method="post" action="{{ route('contents.subcategory', $parent->id) }}"
                  class="space-y-4 rounded-lg bg-white p-6 shadow-sm">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-600">カテゴリ名</label>
                    <input type="text" name="name" required maxlength="120" value="{{ old('name') }}"
                           class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand focus:ring-brand">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600">説明（任意）</label>
                    <div class="mt-1">
                        <x-rich-text name="introduce" :value="old('introduce')" min-height="8rem" placeholder="カテゴリの紹介文…" />
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('contents.index', ['category' => $parent->id]) }}" class="text-sm text-gray-500 hover:underline">キャンセル</a>
                    <button type="submit" class="rounded-lg bg-brand px-5 py-2 text-sm font-medium text-brand-fg hover:bg-brand-dark">追加する</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
