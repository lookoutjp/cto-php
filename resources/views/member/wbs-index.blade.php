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

            <div class="flex justify-end">
                <a href="{{ route('wbs.create') }}" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">ルート項目を追加</a>
            </div>

            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                @if ($roots->isEmpty())
                    <p class="px-5 py-8 text-center text-sm text-gray-400">WBS はまだ登録されていません。</p>
                @else
                    <ul class="divide-y divide-gray-100">
                        @foreach ($roots as $node)
                            @include('member.partials.wbs-node', ['node' => $node, 'depth' => 0])
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
