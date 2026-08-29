<div class="rounded-lg bg-white p-6 shadow-sm">
    <h3 class="mb-3 text-sm font-semibold text-gray-500">関連タスク</h3>

    @if ($conflicts->isNotEmpty())
        <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
            ⚠ 先行タスクの完了予定が、このタスクの開始予定より後です:
            @foreach ($conflicts as $c)
                <span class="font-medium">{{ $c->title }}</span>（{{ optional(\App\Support\TaskRef::endDate($c->model))->isoFormat('M/D') }}）@if (! $loop->last), @endif
            @endforeach
        </div>
    @endif

    <div class="space-y-4 text-sm">
        @foreach ([['先行タスク', $predecessors], ['後続タスク', $successors], ['関連', $related]] as [$label, $items])
            <div>
                <p class="mb-1 text-xs font-medium text-gray-400">{{ $label }}</p>
                @if ($items->isEmpty())
                    <p class="text-xs text-gray-300">なし</p>
                @else
                    <ul class="space-y-1">
                        @foreach ($items as $item)
                            <li class="flex items-center gap-2">
                                <span class="rounded bg-gray-100 px-1 text-[10px] text-gray-500">{{ $item->kind_label }}</span>
                                @if ($item->model)
                                    <a href="{{ $item->kind === 'wbs' ? route('wbs.show', $item->id) : route('tasks.show', [$item->kind, $item->id]) }}"
                                       class="text-gray-800 hover:underline">{{ $item->title }}</a>
                                @else
                                    <span class="text-gray-400">{{ $item->title }}</span>
                                @endif
                                <button wire:click="removeLink({{ $item->relation_id }})"
                                        class="ml-auto text-xs text-gray-300 hover:text-red-600" title="削除">✕</button>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endforeach
    </div>

    <div class="mt-4 border-t border-gray-100 pt-4">
        @error('targetId') <p class="mb-2 text-xs text-red-600">{{ $message }}</p> @enderror
        <div class="flex flex-wrap items-end gap-2">
            <select wire:model.live="linkType" class="rounded-lg border-gray-300 text-xs shadow-sm focus:border-gray-500 focus:ring-gray-500">
                <option value="pred">先行として追加</option>
                <option value="succ">後続として追加</option>
                <option value="rel">関連として追加</option>
            </select>
            <select wire:model.live="targetKind" class="rounded-lg border-gray-300 text-xs shadow-sm focus:border-gray-500 focus:ring-gray-500">
                @foreach ($kinds as $k => $l)
                    <option value="{{ $k }}">{{ $l }}</option>
                @endforeach
            </select>
            <select wire:model="targetId" class="min-w-[10rem] flex-1 rounded-lg border-gray-300 text-xs shadow-sm focus:border-gray-500 focus:ring-gray-500">
                <option value="">選択…</option>
                @foreach ($targetOptions as $tid => $ttitle)
                    <option value="{{ $tid }}">#{{ $tid }} {{ \Illuminate\Support\Str::limit($ttitle, 40) }}</option>
                @endforeach
            </select>
            <button wire:click="addLink" class="rounded-lg bg-gray-900 px-3 py-1.5 text-xs font-medium text-white hover:bg-gray-700">追加</button>
        </div>
    </div>
</div>
