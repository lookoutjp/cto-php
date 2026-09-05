<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            <a href="{{ route('wbs.schedule') }}" class="text-gray-500 hover:underline">スケジュール</a>
            <span class="text-gray-400">/</span> 休日カレンダー
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="space-y-4 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('status') }}</div>
            @endif
            <p class="text-xs text-gray-500">
                スケジュール計算の「稼働日」モードで除外される休日です。土日は自動で非稼働日になります。
            </p>

            <form method="post" action="{{ route('wbs.holidays.store') }}" class="flex items-end gap-2 rounded-lg bg-white p-4 shadow-sm">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-600">日付</label>
                    <input type="date" name="date" required class="mt-1 rounded-lg border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500">
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-600">名称（任意）</label>
                    <input type="text" name="label" maxlength="100" class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500">
                </div>
                <button type="submit" class="rounded-lg bg-brand px-4 py-2 text-sm font-medium text-brand-fg hover:bg-brand-dark">追加</button>
            </form>

            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                @forelse ($holidays as $h)
                    <div class="flex items-center gap-3 border-b border-gray-100 px-4 py-2 text-sm last:border-0">
                        <span class="tabular-nums text-gray-800">{{ $h->date->isoFormat('YYYY年M月D日（ddd）') }}</span>
                        <span class="text-gray-500">{{ $h->label }}</span>
                        <form method="post" action="{{ route('wbs.holidays.destroy', $h->id) }}" class="ml-auto">
                            @csrf @method('DELETE')
                            <button class="text-xs text-gray-300 hover:text-red-600">削除</button>
                        </form>
                    </div>
                @empty
                    <p class="px-4 py-8 text-center text-sm text-gray-400">休日は登録されていません。</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
