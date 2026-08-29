<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            <a href="{{ route('surveys.manage') }}" class="text-gray-500 hover:underline">サーベイ</a>
            <span class="text-gray-400">/</span> {{ $survey->exists ? '編集' : '新規作成' }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <ul class="list-inside list-disc">
                        @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <form method="post"
                  action="{{ $survey->exists ? route('surveys.update', $survey->id) : route('surveys.store') }}"
                  x-data="surveyForm()"
                  class="space-y-5 rounded-lg bg-white p-6 shadow-sm">
                @csrf
                @if ($survey->exists) @method('PUT') @endif

                <div>
                    <label class="block text-xs font-medium text-gray-600">タイトル</label>
                    <input type="text" name="title" required maxlength="255"
                           value="{{ old('title', $survey->title) }}"
                           class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand focus:ring-brand">
                </div>

                <div class="flex flex-wrap gap-6">
                    <div>
                        <label class="block text-xs font-medium text-gray-600">選択可能数</label>
                        <input type="number" name="selectable_numbers" min="1" max="20"
                               value="{{ old('selectable_numbers', $survey->selectable_numbers ?: 1) }}"
                               class="mt-1 w-24 rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand focus:ring-brand">
                        <p class="mt-1 text-xs text-gray-400">2以上で複数選択（チェックボックス）</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600">回答期限</label>
                        <input type="date" name="answer_due_date"
                               value="{{ old('answer_due_date', optional($survey->answer_due_date)->format('Y-m-d')) }}"
                               class="mt-1 rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand focus:ring-brand">
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="hidden" name="open_yn" value="0">
                        <input type="checkbox" name="open_yn" value="1"
                               @checked(old('open_yn', $survey->open_yn ?? true))
                               class="rounded border-gray-300 text-brand focus:ring-brand">
                        受付中にする
                    </label>
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="hidden" name="specify_yn" value="0">
                        <input type="checkbox" name="specify_yn" value="1"
                               @checked(old('specify_yn', $survey->specify_yn ?? false))
                               class="rounded border-gray-300 text-brand focus:ring-brand">
                        記名式にする（誰がどれに投票したか集計で分かる）
                    </label>
                </div>

                <div>
                    <div class="mb-2 flex items-center justify-between">
                        <label class="text-xs font-medium text-gray-600">選択肢</label>
                        @unless ($lockChoices)
                            <button type="button" @click="add()" class="text-xs text-brand hover:underline">＋ 選択肢を追加</button>
                        @endunless
                    </div>

                    @if ($lockChoices)
                        <div class="space-y-2">
                            @foreach ($choices as $c)
                                <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm">
                                    <span class="font-medium text-gray-800">{{ $c->choice_title }}</span>
                                    @if ($c->choice_explain)<p class="mt-0.5 text-xs text-gray-500">{{ $c->choice_explain }}</p>@endif
                                </div>
                            @endforeach
                        </div>
                        <p class="mt-2 text-xs text-amber-600">回答が既にあるため、選択肢は変更できません。</p>
                    @else
                        <div class="space-y-3">
                            <template x-for="(row, i) in rows" :key="i">
                                <div class="rounded-lg border border-gray-200 p-3">
                                    <div class="flex items-start gap-2">
                                        <div class="flex-1 space-y-2">
                                            <input type="text" :name="`choices[${i}][title]`" x-model="row.title"
                                                   placeholder="選択肢" maxlength="255" required
                                                   class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand focus:ring-brand">
                                            <input type="text" :name="`choices[${i}][explain]`" x-model="row.explain"
                                                   placeholder="説明（任意）" maxlength="2000"
                                                   class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand focus:ring-brand">
                                        </div>
                                        <button type="button" @click="remove(i)" x-show="rows.length > 2"
                                                class="mt-1 text-xs text-gray-300 hover:text-red-600">✕</button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('surveys.manage') }}" class="text-sm text-gray-500 hover:underline">キャンセル</a>
                    <button type="submit" class="rounded-lg bg-brand px-5 py-2 text-sm font-medium text-brand-fg hover:bg-brand-dark">
                        {{ $survey->exists ? '更新' : '作成' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function surveyForm() {
            const initial = @json(
                $lockChoices ? [] : (old('choices') ?: $choices->map(fn ($c) => ['title' => $c->choice_title, 'explain' => $c->choice_explain])->all())
            );
            const rows = initial.length >= 2 ? initial.map(r => ({ title: r.title ?? '', explain: r.explain ?? '' }))
                                             : [{ title: '', explain: '' }, { title: '', explain: '' }];
            return {
                rows,
                add() { this.rows.push({ title: '', explain: '' }); },
                remove(i) { if (this.rows.length > 2) this.rows.splice(i, 1); },
            };
        }
    </script>
</x-app-layout>
