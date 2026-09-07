<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">投稿の管理</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl space-y-4 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('status') }}</div>
            @endif

            <p class="text-sm text-gray-500">
                各カテゴリの見出しにある「投稿する」から投稿できます。投稿はカテゴリ管理員の承認後に公開されます。
            </p>

            @forelse ($items as $c)
                @php($state = (int) $c->ok)
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-medium text-gray-900">
                                @if ($state === 1)
                                    <a href="{{ route('contents.show', $c) }}" class="hover:text-brand hover:underline">{{ $c->name }}</a>
                                @else
                                    {{ $c->name }}
                                @endif
                            </p>
                            <p class="mt-0.5 text-xs text-gray-400">
                                {{ $c->sort?->name ?? '—' }} ・ {{ optional($c->adddatetime)->isoFormat('YYYY/MM/DD HH:mm') }}
                            </p>
                        </div>
                        <span @class([
                            'shrink-0 rounded px-2 py-0.5 text-xs font-medium',
                            'bg-green-50 text-green-700' => $state === 1,
                            'bg-amber-100 text-amber-800' => $state === 2,
                            'bg-gray-100 text-gray-600' => $state === 0,
                        ])>
                            {{ $state === 1 ? '公開中' : ($state === 2 ? '承認待ち' : '差し戻し') }}
                        </span>
                    </div>

                    @if ($state === 0 && filled($c->review_note))
                        <div class="mt-2 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-900">
                            <span class="font-medium">差し戻し理由:</span> {{ $c->review_note }}
                        </div>
                    @endif

                    @if ($state === 0)
                        <div class="mt-3 text-right">
                            <a href="{{ route('contents.submit', [$c->content_sort, 'edit' => $c->id]) }}"
                               class="rounded-md bg-brand px-3 py-1.5 text-sm font-medium text-brand-fg hover:bg-brand-dark">
                                編集して再申請
                            </a>
                        </div>
                    @endif
                </div>
            @empty
                <p class="rounded-lg border border-gray-200 bg-white px-4 py-8 text-center text-sm text-gray-500">
                    投稿はまだありません。
                </p>
            @endforelse

            @if ($items->hasPages())
                <div>{{ $items->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
