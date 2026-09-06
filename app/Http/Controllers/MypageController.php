<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Support\CurrentSite;
use App\Support\TaskDashboard;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class MypageController extends Controller
{
    public function __invoke(Request $request): View
    {
        /** @var Member $member */
        $member = $request->user();

        // ninshou 1/-1 以外（閲覧のみ・承認待ち）の会員には簡易版を出す。
        if (! $member->isProjectMemberOf()) {
            return view('mypage-lite', [
                'member' => $member,
                'pendingApproval' => $member->pendingSiteIds()
                    ->contains(app(CurrentSite::class)->idOrNull()),
            ]);
        }

        $dashboard = TaskDashboard::for($member->getKey());

        return view('mypage', [
            'member' => $member,
            'todayTasks' => $dashboard->todayTasks(),
            'hasAnyToday' => $dashboard->hasAnyToday(),
            'statusGrid' => $dashboard->statusGrid(),
            'routineGrid' => $dashboard->routineGrid(),
        ]);
    }
}
