<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            投稿の承認
            @if ($filterCategory)
                <span class="text-gray-400">/</span> <span class="text-base font-normal text-gray-500">{{ $filterCategory->name }}</span>
            @endif
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl space-y-4 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('status') }}</div>
            @endif

            @if ($filterCategory)
                <a href="{{ route('contents.review') }}" class="text-sm text-gray-500 hover:underline">&larr; 管理カテゴリすべての承認待ち</a>
            @endif

            @forelse ($items as $c)
                @include('member.partials.content-review-item', ['c' => $c])
            @empty
                <p class="rounded-lg border border-gray-200 bg-white px-4 py-8 text-center text-sm text-gray-500">
                    承認待ちの投稿はありません。
                </p>
            @endforelse

            @if ($items->hasPages())
                <div>{{ $items->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
