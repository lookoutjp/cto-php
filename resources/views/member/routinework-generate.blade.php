<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            <a href="{{ route('tasks.index', 'routinework') }}" class="text-gray-500 hover:underline">定例作業</a>
            <span class="text-gray-400">/</span> マスターから生成
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-lg px-4 sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
            @endif

            <form method="post" action="{{ route('routinework.generate.run') }}" class="space-y-4 rounded-lg bg-white p-6 shadow-sm">
                @csrf
                <p class="text-xs text-gray-500">
                    定例作業マスター（繰り返しルール）から、指定期間に該当する定例作業を作成します。
                    作成済みの日付は重複して作られません。
                </p>
                <div class="flex flex-wrap gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600">開始日</label>
                        <input type="date" name="start" required value="{{ old('start', $start->format('Y-m-d')) }}"
                               class="mt-1 rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand focus:ring-brand">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600">終了日</label>
                        <input type="date" name="end" required value="{{ old('end', $end->format('Y-m-d')) }}"
                               class="mt-1 rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand focus:ring-brand">
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('tasks.index', 'routinework') }}" class="text-sm text-gray-500 hover:underline">キャンセル</a>
                    <button type="submit" class="rounded-lg bg-brand px-5 py-2 text-sm font-medium text-brand-fg hover:bg-brand-dark">生成</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
