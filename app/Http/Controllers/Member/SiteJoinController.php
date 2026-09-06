<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\MemberRoom;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * 既にログイン中の会員が、別のサイト（テナント）へ「加入申請」する導線。
 * 申請は member_room に applied_at 付き・ninshou NULL の行として作られ、
 * 管理員が Filament「会員権限」画面で承認すると ninshou が付与される。
 *
 * 新規ユーザーの初回登録は Auth\RegisteredUserController（/register）が担当。
 */
class SiteJoinController extends Controller
{
    public function index(): View
    {
        $member = $this->member();

        $memberRooms = MemberRoom::query()
            ->withoutGlobalScope('confirmed')
            ->where('member_id', $member->getKey())
            ->get()
            ->keyBy('site_id');

        // 加入申請を受け付けるサイト = 稼働中 かつ newmemberregfunction 有効。
        $sites = Room::query()
            ->where('site_joutai', 1)
            ->orderBy('sitename')
            ->get()
            ->filter(fn (Room $r) => $r->hasFunction('newmemberregfunction'))
            ->map(function (Room $r) use ($memberRooms) {
                $mr = $memberRooms->get($r->site_id);
                $r->join_state = match (true) {
                    $mr && $mr->isPending() => 'pending',
                    $mr !== null => 'member',
                    ! $r->canAddMembers(1) => 'full',
                    default => 'joinable',
                };
                $r->join_ninshou = $mr && ! $mr->isPending() ? $mr->ninshou : null;

                return $r;
            })
            ->values();

        return view('member.site-join', compact('sites'));
    }

    public function store(Request $request, Room $site): RedirectResponse
    {
        $member = $this->member();

        abort_unless(
            (int) $site->site_joutai === 1 && $site->hasFunction('newmemberregfunction'),
            404,
        );

        $existing = MemberRoom::query()
            ->withoutGlobalScope('confirmed')
            ->where('member_id', $member->getKey())
            ->where('site_id', $site->site_id)
            ->first();

        if ($existing) {
            return back()->with('status', $existing->isPending()
                ? 'このサイトへの加入はすでに申請済みです（承認待ち）。'
                : 'すでにこのサイトのメンバーです。');
        }

        if (! $site->canAddMembers(1)) {
            return back()->with('status', 'このサイトは会員数の上限に達しているため、現在加入申請を受け付けていません。');
        }

        MemberRoom::query()->create([
            'member_id' => $member->getKey(),
            'site_id' => $site->site_id,
            'ninshou' => null,
            'applied_at' => now(),
            'approved_at' => null,
        ]);

        return back()->with('status', "「{$site->sitename}」への加入を申請しました。管理員の承認をお待ちください。");
    }

    private function member(): Member
    {
        $user = Auth::user();
        abort_unless($user instanceof Member, 403);

        return $user;
    }
}
