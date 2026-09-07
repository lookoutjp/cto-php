{{-- props: $c (Content, ok=2), 承認/却下フォーム。/contents/review でもカテゴリ詳細ページでも使う。 --}}
<div x-data="{ rejecting: false, body: false }" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="font-medium text-gray-900">{{ $c->name }}</p>
            <p class="mt-0.5 text-xs text-gray-400">
                {{ $c->sort?->name ?? '—' }} ・
                投稿者: {{ $c->submitter?->name ?? $c->member_id }} ・
                {{ optional($c->adddatetime)->isoFormat('YYYY/MM/DD HH:mm') }}
            </p>
        </div>
        <div class="flex shrink-0 items-center gap-2">
            <button type="button" @click="body = ! body"
                    class="rounded-md border border-gray-300 px-2.5 py-1 text-xs text-gray-600 hover:bg-gray-50">
                <span x-show="!body">本文を見る</span><span x-show="body" x-cloak>閉じる</span>
            </button>
            <form method="post" action="{{ route('contents.approve', $c) }}">
                @csrf
                <button type="submit" class="rounded-md bg-green-600 px-3 py-1 text-xs font-medium text-white hover:bg-green-700">承認</button>
            </form>
            <button type="button" @click="rejecting = ! rejecting"
                    class="rounded-md border border-red-300 px-3 py-1 text-xs font-medium text-red-600 hover:bg-red-50">却下</button>
        </div>
    </div>

    <div x-show="body" x-cloak class="trix-content mt-3 rounded-md border border-gray-100 bg-gray-50 p-3">
        {!! $c->explain !!}
    </div>

    <form x-show="rejecting" x-cloak method="post" action="{{ route('contents.reject', $c) }}" class="mt-3 space-y-2">
        @csrf
        <textarea name="note" required rows="2" maxlength="1000" placeholder="差し戻し理由（投稿者に表示されます）"
                  class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand focus:ring-brand"></textarea>
        <div class="text-right">
            <button type="submit" class="rounded-md bg-red-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-700">
                下書きに差し戻す
            </button>
        </div>
    </form>
</div>
