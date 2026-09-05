<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\LinkItem;
use App\Models\Room;
use App\Support\CurrentSite;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * 旧ASP: aboutsite.asp（サイト概要）/ managerwords.asp（管理員の言葉）/
 * friendlink 系（リンク集）。後者2つは rooms.function_list のフラグで出し分ける。
 */
class SitePageController extends Controller
{
    /**
     * サイト概要（旧 aboutsite.asp）。機能フラグに関係なく常時公開。
     * $site はレイアウト共通の View::composer（AppServiceProvider）が渡す。
     */
    public function about(): View
    {
        return view('public.about');
    }

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
