<div class="mt-8 rounded-lg border border-gray-200 bg-white">
    <div class="flex items-center justify-between border-b border-gray-100 bg-gray-50 px-4 py-3">
        <h2 class="text-sm font-semibold text-gray-700">ユーザーコメント（{{ $comments->total() }}）</h2>
    </div>

    @if (session('comment-status'))
        <div class="border-b border-green-100 bg-green-50 px-4 py-2 text-sm text-green-700">
            {{ session('comment-status') }}
        </div>
    @endif

    <ul class="divide-y divide-gray-100">
        @forelse ($comments as $comment)
            <li class="px-4 py-3">
                <div class="flex items-baseline gap-2 text-xs text-gray-500">
                    <span class="font-medium text-gray-700">{{ $comment->member?->name ?? $comment->member_id ?? '会員' }}</span>
                    @if ($posted = $comment->postedAt())
                        <time class="tabular-nums">{{ $posted->isoFormat('YYYY/MM/DD HH:mm') }}</time>
                    @endif
                </div>
                <p class="mt-1 whitespace-pre-wrap text-sm text-gray-800">{{ $comment->comment }}</p>
            </li>
        @empty
            <li class="px-4 py-6 text-center text-sm text-gray-400">まだコメントはありません。</li>
        @endforelse
    </ul>

    @if ($comments->hasPages())
        <div class="border-t border-gray-100 px-4 py-3">
            {{ $comments->links() }}
        </div>
    @endif

    <div class="border-t border-gray-100 p-4">
        @if ($this->canPost)
            <form wire:submit="submit" class="space-y-2">
                <textarea wire:model="body" rows="3" maxlength="2000"
                          placeholder="コメントを入力"
                          class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500"></textarea>
                @error('body') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                <div class="text-right">
                    <button type="submit"
                            class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                        投稿する
                    </button>
                </div>
            </form>
        @elseif (auth()->check())
            <p class="text-xs text-gray-400">コメントの投稿はこのサイトのプロジェクト参加者のみ可能です。</p>
        @else
            <p class="text-xs text-gray-400">
                <a href="{{ route('login') }}" class="text-blue-600 hover:underline">ログイン</a> するとコメントを投稿できます。
            </p>
        @endif
    </div>
</div>
