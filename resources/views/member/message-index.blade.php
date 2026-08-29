<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">メッセージ</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl space-y-4 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('status') }}</div>
            @endif

            <div class="flex items-center justify-between">
                <div class="flex gap-1 text-sm">
                    <a href="{{ route('messages.index') }}"
                       @class(['rounded-md px-3 py-1.5', 'bg-brand text-brand-fg' => $box === 'inbox', 'text-gray-600 hover:bg-gray-100' => $box !== 'inbox'])>受信箱</a>
                    <a href="{{ route('messages.sent') }}"
                       @class(['rounded-md px-3 py-1.5', 'bg-brand text-brand-fg' => $box === 'sent', 'text-gray-600 hover:bg-gray-100' => $box !== 'sent'])>送信箱</a>
                </div>
                <a href="{{ route('messages.create') }}"
                   class="rounded-lg bg-brand px-4 py-2 text-sm font-medium text-brand-fg hover:bg-brand-dark">新規作成</a>
            </div>

            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                @forelse ($messages as $m)
                    <a href="{{ route('messages.show', $m->id) }}"
                       class="flex items-center gap-3 border-b border-gray-100 px-5 py-3 text-sm last:border-0 hover:bg-gray-50">
                        @if ($box === 'inbox' && ! $m->readed)
                            <span class="h-2 w-2 shrink-0 rounded-full bg-brand"></span>
                        @else
                            <span class="h-2 w-2 shrink-0"></span>
                        @endif
                        <span class="w-28 shrink-0 truncate text-gray-500">
                            {{ $box === 'inbox' ? ($m->sender?->name ?? $m->from) : ($m->recipient?->name ?? $m->to) }}
                        </span>
                        <span class="min-w-0 flex-1 truncate {{ $box === 'inbox' && ! $m->readed ? 'font-medium text-gray-900' : 'text-gray-600' }}">
                            {{ \Illuminate\Support\Str::limit(trim(strip_tags($m->content)), 60) }}
                        </span>
                        <time class="shrink-0 text-xs text-gray-400">{{ optional($m->time)->isoFormat('YY/MM/DD HH:mm') }}</time>
                    </a>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-gray-400">メッセージはありません。</p>
                @endforelse
            </div>

            @if ($messages->hasPages())
                <div>{{ $messages->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
