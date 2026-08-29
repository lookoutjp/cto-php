<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            <a href="{{ route('board.index') }}" class="text-gray-500 hover:underline">掲示板</a>
            <span class="text-gray-400">/</span>
            <a href="{{ route('board.category', $cat->id) }}" class="text-gray-500 hover:underline">{!! $cat->displayName() !!}</a>
            <span class="text-gray-400">/</span> 新規投稿
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
            @endif

            <form method="post" action="{{ route('board.store', $cat->id) }}" class="space-y-4 rounded-lg bg-white p-6 shadow-sm">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-600">タイトル</label>
                    <input type="text" name="title" required maxlength="255" value="{{ old('title') }}"
                           class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600">本文</label>
                    <textarea name="content" rows="10" maxlength="20000"
                              class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500">{{ old('content') }}</textarea>
                </div>
                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('board.category', $cat->id) }}" class="text-sm text-gray-500 hover:underline">キャンセル</a>
                    <button type="submit" class="rounded-lg bg-brand px-5 py-2 text-sm font-medium text-brand-fg hover:bg-brand-dark">投稿</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
