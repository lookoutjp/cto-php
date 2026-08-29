<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\LinkItem;
use App\Models\Room;
use App\Support\CurrentSite;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * 旧ASP: managerwords.asp（管理員の言葉）/ friendlink 系（リンク集）。
 * どちらも公開ページで、rooms.function_list のフラグで出し分ける。
 */
class SitePageController extends Controller
{
    public function managerWords(): View
    {
        $site = $this->siteWithFunction('managerwordsfunction');

        return view('public.manager-words', [
            'shouko' => $site->manager_shouko ?: '管理員',
            'body' => $site->managerwords,
        ]);
    }

    public function links(): View
    {
        $this->siteWithFunction('friendlinkfunction');

        $links = LinkItem::query()->approved()->listingOrder()->get();

        return view('public.links', compact('links'));
    }

    private function siteWithFunction(string $flag): Room
    {
        $site = Room::find(app(CurrentSite::class)->id());

        if (! $site?->hasFunction($flag)) {
            throw new NotFoundHttpException;
        }

        return $site;
    }
}
