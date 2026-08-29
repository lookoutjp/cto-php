{{-- props: $node (Guestbook with `children` relation) --}}
<div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm" style="margin-left: {{ min(($node->space_num - 1) * 1.25, 6) }}rem">
    <p class="font-medium text-gray-900">{{ $node->title }}</p>
    <div class="mt-1 flex items-baseline gap-3 text-xs text-gray-500">
        <span class="font-medium text-gray-700">{{ $node->author?->name ?? $node->user_name }}</span>
        <time class="tabular-nums">{{ optional($node->create_date)->isoFormat('YYYY/MM/DD HH:mm') }}</time>
    </div>
    @if (filled(trim(strip_tags($node->content ?? ''))))
        <div class="prose prose-sm mt-2 max-w-none text-gray-700">{!! $node->content !!}</div>
    @endif

    @if ($node->hasManagerReply())
        <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 p-2.5">
            <p class="text-xs font-semibold text-amber-700">管理員返信</p>
            <div class="prose prose-sm mt-1 max-w-none text-amber-900">{!! $node->revert !!}</div>
        </div>
    @endif

    @include('member.partials.board-reply-form', ['parentId' => $node->id, 'label' => '返信'])
</div>

@foreach ($node->children as $child)
    @include('member.partials.board-reply-node', ['node' => $child])
@endforeach
