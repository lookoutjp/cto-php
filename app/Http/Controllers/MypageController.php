<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Support\TaskDashboard;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class MypageController extends Controller
{
    public function __invoke(Request $request): View
    {
        /** @var Member $member */
        $member = $request->user();

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
