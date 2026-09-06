<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">サイトへの加入</h2>
    </x-slot>

    <div class="py-8">
        <div class="space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            <p class="text-sm text-gray-600">
                参加したいサイトに加入申請を送ります。申請後、そのサイトの管理員が承認すると
                メンバーとして各機能を利用できるようになります（承認までの間も公開コンテンツは閲覧できます）。
            </p>

            @if ($sites->isEmpty())
                <p class="rounded-lg border border-gray-200 bg-white px-4 py-8 text-center text-sm text-gray-500">
                    現在、加入申請を受け付けているサイトはありません。
                </p>
            @else
                <ul class="grid gap-4 sm:grid-cols-2">
                    @foreach ($sites as $site)
                        <li class="rounded-lg border border-gray-200 bg-white p-5">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $site->sitename ?: $site->site_id }}</p>
                                    <p class="text-xs text-gray-400">{{ $site->site_id }}</p>
                                </div>

                                @switch($site->join_state)
                                    @case('member')
                                        <span class="shrink-0 rounded bg-green-50 px-2 py-1 text-xs font-medium text-green-700">
                                            参加中@if ($site->join_ninshou === -1)（管理員）@elseif ($site->join_ninshou === 1)（参加者）@else（閲覧のみ）@endif
                                        </span>
                                        @break
                                    @case('pending')
                                        <span class="shrink-0 rounded bg-amber-100 px-2 py-1 text-xs font-medium text-amber-800">
                                            承認待ち
                                        </span>
                                        @break
                                    @case('full')
                                        <span class="shrink-0 rounded bg-gray-100 px-2 py-1 text-xs font-medium text-gray-500">
                                            受付停止中
                                        </span>
                                        @break
                                    @default
                                        <form method="POST" action="{{ route('site-join.store', $site->site_id) }}" class="shrink-0">
                                            @csrf
                                            <button type="submit"
                                                    class="rounded-md bg-brand px-3 py-1.5 text-sm font-medium text-brand-fg hover:bg-brand-dark">
                                                加入申請する
                                            </button>
                                        </form>
                                @endswitch
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif

            <div class="text-sm">
                <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900 hover:underline">&larr; マイページへ戻る</a>
            </div>
        </div>
    </div>
</x-app-layout>
