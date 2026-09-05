<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            <a href="{{ url()->previous() }}" class="text-gray-500 hover:underline">メッセージ</a>
            <span class="text-gray-400">/</span> 詳細
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="space-y-4 px-4 sm:px-6 lg:px-8">
            <div class="rounded-lg bg-white p-6 shadow-sm">
                <dl class="grid grid-cols-1 gap-y-2 border-b border-gray-100 pb-4 text-sm sm:grid-cols-2">
                    <div class="flex gap-2"><dt class="w-16 shrink-0 text-gray-500">差出人</dt><dd class="text-gray-900">{{ $message->sender?->name ?? $message->from }}</dd></div>
                    <div class="flex gap-2"><dt class="w-16 shrink-0 text-gray-500">宛先</dt><dd class="text-gray-900">{{ $message->recipient?->name ?? $message->to }}</dd></div>
                    <div class="flex gap-2"><dt class="w-16 shrink-0 text-gray-500">日時</dt><dd class="text-gray-900">{{ optional($message->time)->isoFormat('YYYY年M月D日 HH:mm') }}</dd></div>
                </dl>
                <div class="prose prose-sm mt-4 max-w-none">{!! $message->content !!}</div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('messages.create', ['to' => $message->from]) }}"
                   class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">返信</a>
                <form method="post" action="{{ route('messages.destroy', $message->id) }}" onsubmit="return confirm('削除しますか？')">
                    @csrf @method('DELETE')
                    <button class="rounded-lg border border-red-300 px-4 py-2 text-sm text-red-700 hover:bg-red-50">削除</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
