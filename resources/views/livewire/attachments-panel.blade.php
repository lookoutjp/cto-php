<div class="rounded-lg bg-white p-5 shadow-sm">
    <h3 class="mb-3 text-sm font-semibold text-gray-500">添付ファイル</h3>

    @if (session('att-status'))
        <div class="mb-3 rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-700">{{ session('att-status') }}</div>
    @endif

    @if ($attachments->isEmpty())
        <p class="text-sm text-gray-400">添付はありません。</p>
    @else
        <ul class="divide-y divide-gray-100">
            @foreach ($attachments as $a)
                <li class="flex items-center gap-3 py-2">
                    @if ($a->preview_url)
                        <a href="{{ $a->preview_url }}" target="_blank" rel="noopener" class="shrink-0">
                            <img src="{{ $a->preview_url }}" alt="" class="h-12 w-12 rounded object-cover ring-1 ring-gray-200">
                        </a>
                    @elseif ($a->isPdf())
                        <a href="{{ route('attachments.preview', $a->id) }}" target="_blank" rel="noopener"
                           class="flex h-12 w-12 shrink-0 items-center justify-center rounded bg-red-50 text-[10px] font-bold uppercase text-red-600 ring-1 ring-red-100">PDF</a>
                    @else
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded bg-gray-100 text-[10px] font-medium uppercase text-gray-500">{{ $a->ext }}</span>
                    @endif

                    <div class="min-w-0 flex-1">
                        <a href="{{ route('attachments.download', $a->id) }}"
                           class="block truncate text-sm font-medium text-gray-900 hover:text-brand hover:underline">
                            {{ $a->downloadName() }}
                        </a>
                        <p class="text-xs text-gray-400">
                            {{ $a->uploader?->name ?? $a->member_id ?? '—' }}
                            ・{{ optional($a->created_at)->isoFormat('YYYY/MM/DD') }}
                            ・{{ $a->humanSize() }}
                            @if ($a->canPreviewInline() && ! $a->preview_url)
                                ・<a href="{{ route('attachments.preview', $a->id) }}" target="_blank" rel="noopener" class="text-brand hover:underline">プレビュー</a>
                            @endif
                        </p>
                    </div>

                    @if ($this->canRemove($a->member_id))
                        <button type="button" wire:click="remove({{ $a->id }})"
                                wire:confirm="この添付を削除します。よろしいですか？"
                                class="shrink-0 rounded-md px-2 py-1 text-xs text-red-600 hover:bg-red-50">削除</button>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif

    @if ($this->canEdit)
        <form wire:submit="save" class="mt-4 border-t border-gray-100 pt-4">
            <input type="file" wire:model="file"
                   class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-brand file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-brand-fg hover:file:bg-brand-dark">
            @error('file') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

            <div class="mt-2 flex items-center gap-3">
                <button type="submit"
                        class="rounded-lg bg-brand px-4 py-1.5 text-sm font-medium text-brand-fg hover:bg-brand-dark disabled:opacity-50"
                        wire:loading.attr="disabled" wire:target="file,save">
                    <span wire:loading.remove wire:target="file,save">添付する</span>
                    <span wire:loading wire:target="file,save">アップロード中…</span>
                </button>
                <span class="text-xs text-gray-400">最大 {{ \App\Support\FileStorage::humanSize(\App\Support\FileStorage::MAX_BYTES) }}</span>
            </div>
        </form>
    @endif
</div>
