<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            <a href="{{ route('surveys.index') }}" class="text-gray-500 hover:underline">サーベイ</a>
            <span class="text-gray-400">/</span> 作成・管理
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="space-y-4 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('status') }}</div>
            @endif

            <div class="flex justify-end">
                <a href="{{ route('surveys.create') }}"
                   class="rounded-lg bg-brand px-4 py-2 text-sm font-medium text-brand-fg hover:bg-brand-dark">新規作成</a>
            </div>

            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                @forelse ($surveys as $s)
                    <div class="flex flex-wrap items-center gap-3 border-b border-gray-100 px-5 py-3 text-sm last:border-0">
                        <a href="{{ route('surveys.show', $s->id) }}" class="min-w-0 flex-1 truncate font-medium text-gray-900 hover:underline">{{ $s->title }}</a>

                        @if ($s->open_yn)
                            <span class="shrink-0 rounded bg-green-100 px-2 py-0.5 text-xs text-green-700">受付中</span>
                        @else
                            <span class="shrink-0 rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-500">受付終了</span>
                        @endif

                        <span class="shrink-0 text-xs text-gray-400">選択肢 {{ $s->choices_count }}・回答 {{ $s->replies_count }}</span>
                        <span class="shrink-0 text-xs text-gray-400">期限 {{ optional($s->answer_due_date)->isoFormat('YYYY/MM/DD') ?? '—' }}</span>

                        <div class="flex shrink-0 items-center gap-2">
                            <a href="{{ route('surveys.edit', $s->id) }}" class="text-xs text-gray-500 hover:text-gray-900 hover:underline">編集</a>
                            <form method="post" action="{{ route('surveys.toggle-open', $s->id) }}">
                                @csrf
                                <button class="text-xs text-gray-500 hover:text-gray-900 hover:underline">{{ $s->open_yn ? '締切' : '再開' }}</button>
                            </form>
                            <form method="post" action="{{ route('surveys.destroy', $s->id) }}"
                                  onsubmit="return confirm('このサーベイを削除しますか？')">
                                @csrf @method('DELETE')
                                <button class="text-xs text-gray-300 hover:text-red-600">削除</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-gray-400">サーベイはまだありません。</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
