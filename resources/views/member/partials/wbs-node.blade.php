@php($st = optional($node->statusMaster))
<li>
    <div class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50" style="padding-left: {{ 1 + $depth * 1.5 }}rem">
        <span class="shrink-0 text-gray-300">
            @if ($node->kids->isNotEmpty()) ▾ @elseif ($node->iscategory) ▸ @else · @endif
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
        <span class="hidden shrink-0 text-xs text-gray-400 sm:inline">{{ optional($node->assignee)->name }}</span>
        <span class="hidden shrink-0 text-xs tabular-nums sm:inline {{ $node->isOverdue() ? 'text-red-600' : 'text-gray-400' }}">
            {{ optional($node->duedate)->isoFormat('MM/DD') }}
        </span>
    </div>

    @if ($node->kids->isNotEmpty())
        <ul>
            @foreach ($node->kids as $kid)
                @include('member.partials.wbs-node', ['node' => $kid, 'depth' => $depth + 1])
            @endforeach
        </ul>
    @endif
</li>
