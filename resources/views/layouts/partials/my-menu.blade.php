{{-- 旧ASPの「MyMenu」相当。会員画面共通の右サイドバー。プロジェクト参加者のみ業務系セクションを表示 --}}
@php
    $isProjectMember = $site && auth()->user()?->isProjectMemberOf($site->site_id);
@endphp

<div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
    <h2 class="bg-brand px-4 py-2 text-sm font-semibold text-brand-fg">✿ MyMenu</h2>

    <div class="divide-y divide-gray-100">
        <a href="{{ route('profile.edit') }}" class="block bg-brand-bg px-4 py-2 text-sm font-semibold text-brand hover:bg-brand hover:text-brand-fg">
            ユーザ情報
        </a>

        <a href="{{ route('contents.index') }}" class="block bg-brand-bg px-4 py-2 text-sm font-semibold text-brand hover:bg-brand hover:text-brand-fg">
            コンテンツ
        </a>

        @if ($isProjectMember)
            @foreach (\App\Support\TaskKind::all() as $tk)
                @if ($site->hasFunction($tk->function))
                    <div>
                        <div class="bg-brand-bg px-4 py-2 text-sm font-semibold text-brand">
                            {{ \Illuminate\Support\Str::endsWith($tk->label, '管理') ? $tk->label : $tk->label.'管理' }}
                        </div>
                        <ul class="px-4 py-2 text-sm">
                            <li><a href="{{ route('tasks.create', $tk->slug) }}" class="block py-0.5 text-gray-600 hover:text-brand hover:underline">・{{ $tk->slug === 'change' ? '要求起票' : $tk->label.'起票' }}</a></li>
                            <li><a href="{{ route('tasks.index', $tk->slug) }}" class="block py-0.5 text-gray-600 hover:text-brand hover:underline">・私の担当</a></li>
                            <li><a href="{{ route('tasks.index', [$tk->slug, 'view' => 'all']) }}" class="block py-0.5 text-gray-600 hover:text-brand hover:underline">・全員のタスク</a></li>
                        </ul>
                    </div>
                @endif
            @endforeach

            @if ($site->hasFunction('wbsfunction'))
                <div>
                    <div class="bg-brand-bg px-4 py-2 text-sm font-semibold text-brand">WBS管理</div>
                    <ul class="px-4 py-2 text-sm">
                        <li><a href="{{ route('wbs.create') }}" class="block py-0.5 text-gray-600 hover:text-brand hover:underline">・タスク起票</a></li>
                        <li><a href="{{ route('wbs.index') }}" class="block py-0.5 text-gray-600 hover:text-brand hover:underline">・全員のタスク</a></li>
                        <li><a href="{{ route('wbs.load') }}" class="block py-0.5 text-gray-600 hover:text-brand hover:underline">・負荷分析</a></li>
                    </ul>
                </div>
            @endif

            @if ($site->hasFunction('surveyfunction'))
                <div>
                    <div class="bg-brand-bg px-4 py-2 text-sm font-semibold text-brand">サーベイ</div>
                    <ul class="px-4 py-2 text-sm">
                        <li><a href="{{ route('surveys.create') }}" class="block py-0.5 text-gray-600 hover:text-brand hover:underline">・サーベイ作成</a></li>
                        <li><a href="{{ route('surveys.index') }}" class="block py-0.5 text-gray-600 hover:text-brand hover:underline">・回答する</a></li>
                        <li><a href="{{ route('surveys.manage') }}" class="block py-0.5 text-gray-600 hover:text-brand hover:underline">・自分の作成分</a></li>
                    </ul>
                </div>
            @endif
        @endif

        @if ($site?->hasFunction('memberlistfunction'))
            <div>
                <div class="bg-brand-bg px-4 py-2 text-sm font-semibold text-brand">メンバー</div>
                <ul class="px-4 py-2 text-sm">
                    <li><a href="{{ route('members.index') }}" class="block py-0.5 text-gray-600 hover:text-brand hover:underline">・メンバー一覧</a></li>
                    @if ($site->hasFunction('onlinemembersfunction'))
                        <li><a href="{{ route('members.online') }}" class="block py-0.5 text-gray-600 hover:text-brand hover:underline">・オンラインメンバー</a></li>
                    @endif
                </ul>
            </div>
        @endif

        @if ($site?->hasFunction('dengonfunction'))
            <div>
                <div class="bg-brand-bg px-4 py-2 text-sm font-semibold text-brand">メッセージ</div>
                <ul class="px-4 py-2 text-sm">
                    <li><a href="{{ route('messages.index') }}" class="block py-0.5 text-gray-600 hover:text-brand hover:underline">・受信箱</a></li>
                    <li><a href="{{ route('messages.sent') }}" class="block py-0.5 text-gray-600 hover:text-brand hover:underline">・送信済み</a></li>
                    <li><a href="{{ route('messages.create') }}" class="block py-0.5 text-gray-600 hover:text-brand hover:underline">・新規作成</a></li>
                </ul>
            </div>
        @endif

        @if ($site?->hasFunction('filemanagefunction'))
            <a href="{{ route('files.index') }}" class="block bg-brand-bg px-4 py-2 text-sm font-semibold text-brand hover:bg-brand hover:text-brand-fg">
                ファイル
            </a>
        @endif
    </div>
</div>
