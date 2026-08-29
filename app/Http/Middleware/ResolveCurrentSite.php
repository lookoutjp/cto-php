<?php

namespace App\Http\Middleware;

use App\Models\Member;
use App\Models\Room;
use App\Support\CurrentSite;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * リクエストごとに CurrentSite を確定させる。
 *
 *  1. session('site_id') が実在サイトなら採用
 *  2. なければログイン中 Member の所属サイト（先頭）を採用し session に保存
 *  3. どちらも無ければ未確定のまま（BelongsToSite のスコープは無効）
 *
 * 実在サイトの集合は rooms テーブル。存在しない site_id がセッションに
 * 残っている場合（サイト削除など）はクリアする。
 */
class ResolveCurrentSite
{
    public function handle(Request $request, Closure $next): Response
    {
        $current = app(CurrentSite::class);

        $validSiteIds = Room::query()->pluck('site_id')->all();

        $sessionSite = $request->session()->get('site_id');
        if ($sessionSite !== null && in_array($sessionSite, $validSiteIds, true)) {
            $current->set($sessionSite);

            return $next($request);
        }

        if ($sessionSite !== null) {
            $request->session()->forget('site_id');
        }

        $user = $request->user();
        if ($user instanceof Member) {
            $memberSite = $user->rooms()->first()?->site_id;
            if ($memberSite !== null) {
                $current->set($memberSite);
                $request->session()->put('site_id', $memberSite);
            }
        }

        return $next($request);
    }
}
