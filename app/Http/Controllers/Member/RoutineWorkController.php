<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Support\CurrentSite;
use App\Support\RoutineWorkGenerator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * 定例作業マスターからの一括生成。旧ASP: RoutineWorkMake.asp。
 * プロジェクト参加者が期間を指定して実行する（自動実行は artisan routinework:generate）。
 */
class RoutineWorkController extends Controller
{
    public function generateForm(): View
    {
        $this->ensureEnabled();

        return view('member.routinework-generate', [
            'start' => Carbon::today(),
            'end' => Carbon::today()->addDays(31),
        ]);
    }

    public function generate(Request $request): RedirectResponse
    {
        $this->ensureEnabled();

        $data = $request->validate([
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after_or_equal:start'],
        ], [], ['start' => '開始日', 'end' => '終了日']);

        $result = (new RoutineWorkGenerator(
            Carbon::parse($data['start']),
            Carbon::parse($data['end']),
        ))->run();

        return redirect()->route('tasks.index', 'routinework')->with(
            'status',
            "マスター {$result['masters']} 件から、定例作業を {$result['created']} 件作成しました。"
        );
    }

    private function ensureEnabled(): void
    {
        if (! Room::find(app(CurrentSite::class)->id())?->hasFunction('routineworkfunction')) {
            throw new NotFoundHttpException;
        }
    }
}
