<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Wbs;
use App\Support\CurrentSite;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * 会員向け WBS（階層表示）。旧 wbs.asp 相当。
 * v1 は閲覧のみ（ツリー + 詳細）。編集・並び替えは未実装。
 */
class WbsController extends Controller
{
    public function index(): View
    {
        $this->ensureEnabled();

        return view('member.wbs-index', ['roots' => Wbs::tree()]);
    }

    public function show(int $id): View
    {
        $this->ensureEnabled();

        $node = Wbs::query()->notDeleted()
            ->with(['statusMaster', 'assignee', 'creator', 'team', 'parent'])
            ->findOrFail($id);

        return view('member.wbs-show', [
            'node' => $node,
            'children' => $node->children()->notDeleted()->with(['statusMaster', 'assignee'])->get(),
        ]);
    }

    private function ensureEnabled(): void
    {
        if (! Room::find(app(CurrentSite::class)->id())?->hasFunction('wbsfunction')) {
            throw new NotFoundHttpException;
        }
    }
}
