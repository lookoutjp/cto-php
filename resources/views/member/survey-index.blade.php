<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">サーベイ</h2>
    </x-slot>

    <div class="py-8">
        <div class="space-y-4 px-4 sm:px-6 lg:px-8">
            <div class="flex justify-end">
                <a href="{{ route('surveys.manage') }}"
                   class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    作成・管理
                </a>
            </div>
            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                @if ($surveys->isEmpty())
                    <p class="px-5 py-8 text-center text-sm text-gray-400">回答できるサーベイはありません。</p>
                @else
                    <ul class="divide-y divide-gray-100">
                        @foreach ($surveys as $s)
                            <li class="flex items-center gap-3 px-5 py-3">
                                <a href="{{ route('surveys.show', $s->id) }}" class="min-w-0 flex-1 truncate font-medium text-gray-900 hover:underline">
                                    {{ $s->title }}
                                </a>
                                @if ($repliedIds->has($s->id))
                                    <span class="shrink-0 rounded bg-green-100 px-2 py-0.5 text-xs text-green-700">回答済み</span>
                                @elseif ($s->isPastDue())
                                    <span class="shrink-0 rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-500">受付終了</span>
                                @else
                                    <span class="shrink-0 rounded bg-amber-100 px-2 py-0.5 text-xs text-amber-700">未回答</span>
                                @endif
                                <span class="hidden shrink-0 text-xs text-gray-400 sm:inline">
                                    期限 {{ optional($s->answer_due_date)->isoFormat('YYYY/MM/DD') ?? '—' }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
