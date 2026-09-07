{{-- props: $parentId, $label --}}
<div x-data="{ open: false }" class="mt-3">
    <button type="button" @click="open = !open"
            class="rounded-md border border-gray-300 px-2.5 py-1 text-xs text-gray-600 hover:bg-gray-50">
        <span x-show="!open">{{ $label ?? '返信' }}</span>
        <span x-show="open" x-cloak>閉じる</span>
    </button>

    <form x-show="open" x-cloak method="post" action="{{ route('board.reply', $parentId) }}" class="mt-2 space-y-2">
        @csrf
        <input type="text" name="title" required maxlength="255" placeholder="タイトル"
               class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500">
        {{-- 返信フォームは初期非表示。開いたときに Trix を初期化する（幅0で初期化される問題の回避）。 --}}
        <template x-if="open">
            <x-rich-text name="content" placeholder="返信を入力…" min-height="6rem" />
        </template>
        <div class="text-right">
            <button type="submit" class="rounded-lg bg-brand px-4 py-1.5 text-sm font-medium text-brand-fg hover:bg-brand-dark">投稿</button>
        </div>
    </form>
</div>
