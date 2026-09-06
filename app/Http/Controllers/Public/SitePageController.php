<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\LinkItem;
use App\Models\Room;
use App\Support\AdminMode;
use App\Support\CurrentSite;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    /**
     * `/{site}/…` — 独自ドメインを使わずに、共有ドメイン上でテナントの公開フロントを開く。
     *   例: https://cto.jp/miraipmo/         → session に miraipmo を保存して / へ
     *       https://cto.jp/demo/contents/5   → session に demo を保存して /contents/5 へ
     *
     * 保存先は session('site_view')。ResolveCurrentSite が「表示中サイト」として最優先で使う。
     * 実在しない {site} は 404（ワイルドカードなので通常の 404 と同じ挙動）。
     */
    public function enter(Request $request, string $site, ?string $path = null): RedirectResponse
    {
        abort_unless(Room::whereKey($site)->exists(), 404);

        $request->session()->put('site_view', $site);

        $target = '/'.ltrim((string) $path, '/');
        if ($query = $request->getQueryString()) {
            $target .= '?'.$query;
        }

        return redirect()->to($target);
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
        $site = $this->siteWithFunction('friendlinkfunction');

        // 管理者モードでは未承認（allow=0）のリンクも薄く表示し、その場で編集できるようにする。
        $adminMode = AdminMode::activeFor($site->site_id);

        $links = LinkItem::query()
            ->when(! $adminMode, fn ($q) => $q->approved())
            ->listingOrder()
            ->get();

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
