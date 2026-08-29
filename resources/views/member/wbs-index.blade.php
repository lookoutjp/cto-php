<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">WBS</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
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
