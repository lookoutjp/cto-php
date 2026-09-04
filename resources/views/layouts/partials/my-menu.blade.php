{{--
    旧ASPの「MyMenu」相当。会員画面共通の右サイドバー。
    各セクションはクリックで開閉する折りたたみ式（旧ASPと同じ挙動）。
    現在地に対応するセクションは強調表示＋初期状態で開く。
--}}
@php
    $isProjectMember = $site && auth()->user()?->isProjectMemberOf($site->site_id);

    // ヘッダーの共通クラス（開いている/現在地なら強調）
    $sectionHeaderClass = fn (bool $active) => $active
        ? 'flex w-full items-center justify-between bg-brand px-4 py-2 text-left text-sm font-semibold text-brand-fg'
        : 'flex w-full items-center justify-between bg-brand-bg px-4 py-2 text-left text-sm font-semibold text-brand hover:bg-brand hover:text-brand-fg';
@endphp

@php($chevron = <<<'HTML'
    <svg class="h-4 w-4 shrink-0 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
    </svg>
    HTML)

<div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
    <h2 class="bg-brand px-4 py-2 text-sm font-semibold text-brand-fg">✿ MyMenu</h2>

    <div class="divide-y divide-gray-100">
        @if ($site && auth()->user()?->managesSite($site->site_id))
            <a href="/admin" class="block bg-brand-bg px-4 py-2 text-sm font-semibold text-brand hover:bg-brand hover:text-brand-fg">
                ⚙ 管理画面（メニュー・カテゴリ等の編集）
            </a>
        @endif

        <a href="{{ route('profile.edit') }}" class="block bg-brand-bg px-4 py-2 text-sm font-semibold text-brand hover:bg-brand hover:text-brand-fg">
            ユーザ情報
        </a>

        <a href="{{ route('contents.index') }}" class="block bg-brand-bg px-4 py-2 text-sm font-semibold text-brand hover:bg-brand hover:text-brand-fg">
            コンテンツ
        </a>

        @if ($isProjectMember)
            @foreach (\App\Support\TaskKind::all() as $tk)
                @if ($site->hasFunction($tk->function))
                    @php($active = request()->routeIs('tasks.*') && request()->route('kind') === $tk->slug)
                    <div x-data="{ open: @js($active) }">
                        <button type="button" @click="open = ! open" class="{{ $sectionHeaderClass($active) }}">
                            <span>{{ \Illuminate\Support\Str::endsWith($tk->label, '管理') ? $tk->label : $tk->label.'管理' }}</span>
                            {!! $chevron !!}
                        </button>
                        <ul x-show="open" x-collapse class="px-4 py-2 text-sm">
                            <li><a href="{{ route('tasks.create', $tk->slug) }}" class="block py-0.5 text-gray-600 hover:text-brand hover:underline">・{{ $tk->slug === 'change' ? '要求起票' : $tk->label.'起票' }}</a></li>
                            <li><a href="{{ route('tasks.index', $tk->slug) }}" class="block py-0.5 text-gray-600 hover:text-brand hover:underline">・私の担当</a></li>
                            <li><a href="{{ route('tasks.index', [$tk->slug, 'view' => 'all']) }}" class="block py-0.5 text-gray-600 hover:text-brand hover:underline">・全員のタスク</a></li>
                        </ul>
                    </div>
                @endif
            @endforeach

            @if ($site->hasFunction('wbsfunction'))
                @php($active = request()->routeIs('wbs.*'))
                <div x-data="{ open: @js($active) }">
                    <button type="button" @click="open = ! open" class="{{ $sectionHeaderClass($active) }}">
                        <span>WBS管理</span>
                        {!! $chevron !!}
                    </button>
                    <ul x-show="open" x-collapse class="px-4 py-2 text-sm">
                        <li><a href="{{ route('wbs.create') }}" class="block py-0.5 text-gray-600 hover:text-brand hover:underline">・タスク起票</a></li>
                        <li><a href="{{ route('wbs.index') }}" class="block py-0.5 text-gray-600 hover:text-brand hover:underline">・全員のタスク</a></li>
                        <li><a href="{{ route('wbs.load') }}" class="block py-0.5 text-gray-600 hover:text-brand hover:underline">・負荷分析</a></li>
                    </ul>
                </div>
            @endif

            @if ($site->hasFunction('surveyfunction'))
                @php($active = request()->routeIs('surveys.*'))
                <div x-data="{ open: @js($active) }">
                    <button type="button" @click="open = ! open" class="{{ $sectionHeaderClass($active) }}">
                        <span>サーベイ</span>
                        {!! $chevron !!}
                    </button>
                    <ul x-show="open" x-collapse class="px-4 py-2 text-sm">
                        <li><a href="{{ route('surveys.create') }}" class="block py-0.5 text-gray-600 hover:text-brand hover:underline">・サーベイ作成</a></li>
                        <li><a href="{{ route('surveys.index') }}" class="block py-0.5 text-gray-600 hover:text-brand hover:underline">・回答する</a></li>
                        <li><a href="{{ route('surveys.manage') }}" class="block py-0.5 text-gray-600 hover:text-brand hover:underline">・自分の作成分</a></li>
                    </ul>
                </div>
            @endif

            @if ($site->hasFunction('freeguestbookfunction'))
                <a href="{{ route('board.index') }}" @class([$sectionHeaderClass(request()->routeIs('board.*')), 'justify-start' => true])>
                    コミュニティ
                </a>
            @endif
        @endif

        @if ($site?->hasFunction('memberlistfunction'))
            @php($active = request()->routeIs('members.*'))
            <div x-data="{ open: @js($active) }">
                <button type="button" @click="open = ! open" class="{{ $sectionHeaderClass($active) }}">
                    <span>メンバー状況</span>
                    {!! $chevron !!}
                </button>
                <ul x-show="open" x-collapse class="px-4 py-2 text-sm">
                    <li><a href="{{ route('members.index') }}" class="block py-0.5 text-gray-600 hover:text-brand hover:underline">・メンバー一覧</a></li>
                    @if ($site->hasFunction('onlinemembersfunction'))
                        <li><a href="{{ route('members.online') }}" class="block py-0.5 text-gray-600 hover:text-brand hover:underline">・オンラインメンバー</a></li>
                    @endif
                </ul>
            </div>
        @endif

        @if ($site?->hasFunction('dengonfunction'))
            @php($active = request()->routeIs('messages.*'))
            <div x-data="{ open: @js($active) }">
                <button type="button" @click="open = ! open" class="{{ $sectionHeaderClass($active) }}">
                    <span>メッセージ</span>
                    {!! $chevron !!}
                </button>
                <ul x-show="open" x-collapse class="px-4 py-2 text-sm">
                    <li><a href="{{ route('messages.index') }}" class="block py-0.5 text-gray-600 hover:text-brand hover:underline">・受信箱</a></li>
                    <li><a href="{{ route('messages.sent') }}" class="block py-0.5 text-gray-600 hover:text-brand hover:underline">・送信済み</a></li>
                    <li><a href="{{ route('messages.create') }}" class="block py-0.5 text-gray-600 hover:text-brand hover:underline">・新規作成</a></li>
                </ul>
            </div>
        @endif

        @if ($site?->hasFunction('filemanagefunction'))
            <a href="{{ route('files.index') }}" class="{{ $sectionHeaderClass(request()->routeIs('files.*')) }}">
                ファイル管理
            </a>
        @endif
    </div>
</div>
