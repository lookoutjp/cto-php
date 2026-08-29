<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\MemberRoom;
use App\Models\Room;
use App\Support\CurrentSite;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * サイト参加者の一覧。旧ASP: memberlist.asp（ninshou <> 0 の会員）。
 * memberlistfunction が有効なサイトのみ。
 */
class MemberListController extends Controller
{
    public function index(): View
    {
        $siteId = app(CurrentSite::class)->id();

        if (! Room::find($siteId)?->hasFunction('memberlistfunction')) {
            throw new NotFoundHttpException;
        }

        $memberIds = MemberRoom::query()
            ->where('site_id', $siteId)
            ->whereIn('ninshou', [1, -1])
            ->pluck('member_id');

        $members = Member::query()
            ->whereIn('member_id', $memberIds)
            ->orderBy('name')
            ->get(['member_id', 'name', 'nameread', 'introduce', 'appeal', 'online', 'hp']);

        return view('member.member-list', compact('members'));
    }
}
