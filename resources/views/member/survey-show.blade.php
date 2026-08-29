<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            <a href="{{ route('surveys.index') }}" class="text-gray-500 hover:underline">サーベイ</a>
            <span class="text-gray-400">/</span> {{ \Illuminate\Support\Str::limit($survey->title, 30) }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="rounded-lg bg-white p-6 shadow-sm">
                <h1 class="text-lg font-bold text-gray-900">{{ $survey->title }}</h1>
                <p class="mt-1 text-xs text-gray-400">
                    期限 {{ optional($survey->answer_due_date)->isoFormat('YYYY年M月D日') ?? '—' }}
                    ・{{ $survey->isMultiSelect() ? $survey->selectable_numbers.'つまで選択' : '1つ選択' }}
                </p>

                @if ($canAnswer)
                    <form method="post" action="{{ route('surveys.answer', $survey->id) }}" class="mt-5 space-y-3">
                        @csrf
                        @foreach ($survey->choices as $choice)
                            <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 p-3 hover:bg-gray-50">
                                <input type="{{ $survey->isMultiSelect() ? 'checkbox' : 'radio' }}"
                                       name="choices[]" value="{{ $choice->choice_number }}"
                                       class="mt-0.5 {{ $survey->isMultiSelect() ? 'rounded' : '' }} border-gray-300 text-gray-900 focus:ring-gray-500">
                                <span>
                                    <span class="font-medium text-gray-900">{{ $choice->choice_title }}</span>
                                    @if ($choice->choice_explain)
                                        <span class="mt-0.5 block text-sm text-gray-500">{{ $choice->choice_explain }}</span>
                                    @endif
                                </span>
                            </label>
                        @endforeach

                        <div class="pt-2">
                            <button type="submit" class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-gray-700">
                                回答する
                            </button>
                        </div>
                    </form>
                @else
                    <div class="mt-5">
                        <p class="mb-3 text-sm text-gray-500">
                            @if ($hasReplied) ご回答ありがとうございました。現在の集計結果です。
                            @else 受付は終了しました。集計結果です。
                            @endif
                            （回答者 {{ $survey->respondentCount() }} 名）
                        </p>
                        @php($total = max(1, $tally->sum()))
                        <ul class="space-y-3">
                            @foreach ($survey->choices as $choice)
                                @php($count = (int) ($tally[$choice->choice_number] ?? 0))
                                <li>
                                    <div class="flex items-baseline justify-between text-sm">
                                        <span class="text-gray-900">{{ $choice->choice_title }}</span>
                                        <span class="tabular-nums text-gray-500">{{ $count }}票（{{ round($count / $total * 100) }}%）</span>
                                    </div>
                                    <div class="mt-1 h-2 overflow-hidden rounded bg-gray-100">
                                        <div class="h-full rounded bg-gray-800" style="width: {{ round($count / $total * 100) }}%"></div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
