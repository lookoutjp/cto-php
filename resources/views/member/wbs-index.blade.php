<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">WBS</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl space-y-4 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
            @endif

            <div class="flex items-center justify-between">
                <p class="text-xs text-gray-400">⠿ をドラッグして並び替え・階層変更ができます</p>
                <div class="flex gap-2">
                    <a href="{{ route('wbs.schedule') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">スケジュール計算</a>
                    <a href="{{ route('wbs.check') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">計画チェック</a>
                    <a href="{{ route('wbs.create') }}" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">ルート項目を追加</a>
                </div>
            </div>

            <div class="rounded-lg bg-white p-2 shadow-sm">
                @if ($roots->isEmpty())
                    <p class="px-3 py-8 text-center text-sm text-gray-400">WBS はまだ登録されていません。</p>
                @endif
                <ul id="wbs-root" class="wbs-sortable min-h-[8px]" data-parent-id="0">
                    @foreach ($roots as $node)
                        @include('member.partials.wbs-node', ['node' => $node])
                    @endforeach
                </ul>
            </div>

            <p id="wbs-save-note" class="hidden text-xs text-gray-400"></p>
        </div>
    </div>
</x-app-layout>
