{{-- props: $cat（ContentSort）。ログイン会員向けのカテゴリ操作ボタン行。
     $isSiteMember / $manageableCatIds / $pendingCatIds / $pendingCountByCat / $adminMode は
     contents-index / category-node のスコープから継承。 --}}
@php
    $isMember = $isSiteMember ?? false;
    $canManage = in_array((int) $cat->id, $manageableCatIds ?? [], true);
    $applied = in_array((int) $cat->id, $pendingCatIds ?? [], true);
    $pendingN = ($pendingCountByCat ?? [])[$cat->id] ?? 0;
@endphp

@if ($isMember && ! ($adminMode ?? false))
    <span class="inline-flex flex-wrap items-center gap-1.5 align-middle">
        <a href="{{ route('contents.submit', $cat->id) }}"
           class="rounded-md border border-white/60 bg-white/90 px-2 py-0.5 text-xs font-medium text-brand hover:bg-white">
            {{ $canManage ? '＋投稿を追加' : '投稿する' }}
        </a>

        @if ($canManage)
            <a href="{{ route('contents.subcategory', $cat->id) }}"
               class="rounded-md border border-white/60 bg-white/90 px-2 py-0.5 text-xs font-medium text-brand hover:bg-white">
                ＋サブカテゴリ
            </a>
            @if ($pendingN > 0)
                <a href="{{ route('contents.review', ['category' => $cat->id]) }}"
                   class="rounded-md bg-amber-400 px-2 py-0.5 text-xs font-semibold text-amber-950 hover:bg-amber-300">
                    承認待ち {{ $pendingN }}
                </a>
            @endif
        @elseif ($applied)
            <span class="rounded-md border border-white/50 px-2 py-0.5 text-xs text-white/90">管理員申請中</span>
        @else
            <form method="post" action="{{ route('contents.apply-manager', $cat->id) }}" class="inline">
                @csrf
                <button type="submit"
                        class="rounded-md border border-white/60 bg-white/90 px-2 py-0.5 text-xs font-medium text-brand hover:bg-white">
                    カテゴリ管理員になる
                </button>
            </form>
        @endif
    </span>
@endif
