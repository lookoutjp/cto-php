<x-layouts.public :title="$shouko.'の言葉'">
    <article class="rounded-lg border border-gray-200 bg-white p-6">
        @if (filled($body))
            <div class="prose prose-sm max-w-none prose-a:text-blue-600">
                {!! $body !!}
            </div>
        @else
            <p class="text-sm text-gray-400">まだ登録されていません。</p>
        @endif
    </article>
</x-layouts.public>
