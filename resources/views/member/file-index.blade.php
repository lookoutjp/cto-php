@php use App\Support\FileStorage; @endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">ファイル</h2>
    </x-slot>

    <div class="py-8">
        <div class="space-y-5 px-4 sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
            @endif

            {{-- アップロード --}}
            <form method="post" action="{{ route('files.store') }}" enctype="multipart/form-data"
                  class="space-y-3 rounded-lg bg-white p-5 shadow-sm">
                @csrf
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">ファイル</label>
                        <input type="file" name="file" required
                               class="mt-1 block w-full text-sm text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-brand file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-brand-fg hover:file:bg-brand-dark">
                        <p class="mt-1 text-xs text-gray-400">
                            最大 {{ FileStorage::humanSize(FileStorage::MAX_BYTES) }}。
                            {{ implode(' / ', array_slice(FileStorage::ALLOWED_EXTENSIONS, 0, 12)) }} など
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">説明（任意）</label>
                        <input type="text" name="intro" value="{{ old('intro') }}" maxlength="2000"
                               class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-brand focus:ring-brand">
                    </div>
                </div>

                @if ($tags->isNotEmpty())
                    <div>
                        <span class="block text-sm font-medium text-gray-700">タグ（任意）</span>
                        <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1">
                            @foreach ($tags as $t)
                                <label class="inline-flex items-center gap-1.5 text-sm text-gray-600">
                                    <input type="checkbox" name="tag_ids[]" value="{{ $t->tag_id }}"
                                           class="rounded border-gray-300 text-brand focus:ring-brand">
                                    {{ $t->tagname }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="flex items-center justify-between">
                    <p class="text-xs text-gray-400">
                        使用量 {{ FileStorage::humanSize($storageUsed) }}
                        @if ($storageLimitMb !== null) / {{ number_format($storageLimitMb) }} MB @else（無制限）@endif
                    </p>
                    <button type="submit" class="rounded-lg bg-brand px-5 py-2 text-sm font-medium text-brand-fg hover:bg-brand-dark">
                        アップロード
                    </button>
                </div>
            </form>

            {{-- タグ絞り込み --}}
            @if ($tags->isNotEmpty())
                <div class="flex flex-wrap gap-1 text-sm">
                    <a href="{{ route('files.index') }}"
                       @class(['rounded-md px-3 py-1', 'bg-brand text-brand-fg' => ! $activeTag, 'text-gray-600 hover:bg-gray-100' => $activeTag])>すべて</a>
                    @foreach ($tags as $t)
                        <a href="{{ route('files.index', ['tag' => $t->tag_id]) }}"
                           @class(['rounded-md px-3 py-1', 'bg-brand text-brand-fg' => $activeTag === (int) $t->tag_id, 'text-gray-600 hover:bg-gray-100' => $activeTag !== (int) $t->tag_id])>{{ $t->tagname }}</a>
                    @endforeach
                </div>
            @endif

            {{-- 一覧 --}}
            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                @forelse ($files as $file)
                    <div class="flex items-start gap-3 border-b border-gray-100 px-5 py-3 text-sm last:border-0">
                        @if ($file->preview_url ?? null)
                            <a href="{{ $file->preview_url }}" target="_blank" rel="noopener" class="shrink-0">
                                <img src="{{ $file->preview_url }}" alt="" class="h-12 w-12 rounded object-cover ring-1 ring-gray-200">
                            </a>
                        @endif
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5">
                                <span class="font-medium text-gray-900">{{ $file->downloadName() }}</span>
                                <span class="rounded bg-gray-100 px-1.5 py-0.5 text-xs uppercase text-gray-500">{{ $file->fileext }}</span>
                                @foreach ($tags->whereIn('tag_id', $file->tagIds()) as $t)
                                    <span class="rounded bg-brand-bg px-1.5 py-0.5 text-xs text-brand">{{ $t->tagname }}</span>
                                @endforeach
                            </div>
                            @if ($file->intro)
                                <p class="mt-0.5 text-gray-600">{{ $file->intro }}</p>
                            @endif
                            <p class="mt-0.5 text-xs text-gray-400">
                                {{ $file->uploader?->name ?? $file->member_id ?? '—' }}
                                ・{{ optional($file->adddt)->isoFormat('YYYY/MM/DD') ?? '—' }}
                                ・{{ FileStorage::humanSize($file->size_bytes) }}
                                @unless ($file->hasBytes())
                                    <span class="text-amber-600">（実体未移行）</span>
                                @endunless
                            </p>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            @if ($file->hasBytes() && ! ($file->preview_url ?? null) && FileStorage::canPreviewInline($file->fileext))
                                <a href="{{ route('files.preview', $file->id) }}" target="_blank" rel="noopener"
                                   class="rounded-md border border-gray-200 px-2.5 py-1 text-xs text-gray-700 hover:bg-gray-50">プレビュー</a>
                            @endif
                            @if ($file->hasBytes())
                                <a href="{{ route('files.download', $file->id) }}"
                                   class="rounded-md border border-gray-200 px-2.5 py-1 text-xs text-gray-700 hover:bg-gray-50">ダウンロード</a>
                            @endif
                            <form method="post" action="{{ route('files.destroy', $file->id) }}"
                                  onsubmit="return confirm('このファイルを削除します。よろしいですか？')">
                                @csrf @method('DELETE')
                                <button type="submit" class="rounded-md px-2 py-1 text-xs text-red-600 hover:bg-red-50">削除</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-gray-400">ファイルはありません。</p>
                @endforelse
            </div>

            @if ($files->hasPages())
                <div>{{ $files->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
