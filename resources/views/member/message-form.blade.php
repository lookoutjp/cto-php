<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            <a href="{{ route('messages.index') }}" class="text-gray-500 hover:underline">メッセージ</a>
            <span class="text-gray-400">/</span> 新規作成
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="px-4 sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
            @endif

            <form method="post" action="{{ route('messages.store') }}" class="space-y-4 rounded-lg bg-white p-6 shadow-sm">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-600">宛先</label>
                    <div class="mt-1">
                        <x-member-picker name="to" :members="$members" :value="old('to', request('to'))" />
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600">本文</label>
                    <div class="mt-1">
                        <x-rich-text name="content" :value="old('content')" min-height="12rem" />
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('messages.index') }}" class="text-sm text-gray-500 hover:underline">キャンセル</a>
                    <button type="submit" class="rounded-lg bg-brand px-5 py-2 text-sm font-medium text-brand-fg hover:bg-brand-dark">送信</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
