@php($st = optional($node->statusMaster))
<li data-id="{{ $node->id }}" class="wbs-item">
    <div class="group flex items-center gap-2 border-b border-gray-50 px-2 py-1.5 hover:bg-gray-50">
        <span class="wbs-handle shrink-0 cursor-grab select-none px-1 text-gray-300 hover:text-gray-500" title="ドラッグで移動">⠿</span>
        <span class="w-3 shrink-0 text-center text-gray-300">
            @if ($node->iscategory) ▸ @else · @endif
        </span>
        <a href="{{ route('wbs.show', $node->id) }}"
           class="min-w-0 flex-1 truncate {{ $node->iscategory ? 'font-semibold text-gray-900' : 'text-gray-700' }} hover:underline">
            {{ $node->title }}
        </a>

        @if ($st->statusname)
            <span @class([
                'shrink-0 rounded px-1.5 py-0.5 text-xs',
                'bg-gray-100 text-gray-600' => (int) $st->percent === 0,
                'bg-blue-100 text-blue-700' => $st->percent > 0 && $st->percent < 100,
                'bg-green-100 text-green-700' => (int) $st->percent === 100,
                'bg-amber-100 text-amber-700' => in_array((int) $st->percent, [-1, -2], true),
            ])>{{ $st->statusname }}</span>
        @endif
        <span class="hidden w-24 shrink-0 truncate text-right text-xs text-gray-400 sm:inline">{{ optional($node->assignee)->name }}</span>
        <span class="hidden w-12 shrink-0 text-right text-xs tabular-nums sm:inline {{ $node->isOverdue() ? 'text-red-600' : 'text-gray-400' }}">
            {{ optional($node->duedate)->isoFormat('MM/DD') }}
        </span>

        <span class="flex shrink-0 items-center gap-0.5 opacity-0 transition group-hover:opacity-100">
            <a href="{{ route('wbs.create', ['parent' => $node->id]) }}" class="rounded px-1 text-xs text-gray-400 hover:bg-gray-200 hover:text-gray-700" title="子項目を追加">＋</a>
            <a href="{{ route('wbs.edit', $node->id) }}" class="rounded px-1 text-xs text-gray-400 hover:bg-gray-200 hover:text-gray-700" title="編集">✎</a>
            <form method="post" action="{{ route('wbs.destroy', $node->id) }}" onsubmit="return confirm('削除しますか？')">
                @csrf @method('DELETE')
                <button class="rounded px-1 text-xs text-gray-400 hover:bg-red-100 hover:text-red-700" title="削除">✕</button>
            </form>
        </span>
    </div>

    <ul class="wbs-sortable ms-6 min-h-[6px]" data-parent-id="{{ $node->id }}">
        @foreach ($node->kids as $kid)
            @include('member.partials.wbs-node', ['node' => $kid])
        @endforeach
    </ul>
</li>
