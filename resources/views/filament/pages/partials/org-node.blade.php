<li>
    <div class="flex items-center gap-2 py-1">
        <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 font-medium text-gray-800 dark:bg-white/10 dark:text-gray-100">
            {{ $node->levelname ?: '（無名 #'.$node->level.'）' }}
        </span>
        <span class="text-xs text-gray-400">#{{ $node->level }}</span>
    </div>

    @if ($node->kids->isNotEmpty())
        <ul>
            @foreach ($node->kids as $child)
                @include('filament.pages.partials.org-node', ['node' => $child, 'depth' => $depth + 1])
            @endforeach
        </ul>
    @endif
</li>
