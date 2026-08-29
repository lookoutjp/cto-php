<?php

namespace App\Http\Middleware;

use App\Models\Member;
use App\Support\CurrentSite;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * リクエストごとに CurrentSite を確定させる。
 *
 * ログイン中 Member が対象にできるサイト = manageableSiteIds()
 * （管理員 = 旧ASP ninshou -1、またはスーパー管理者）。
 * 現状データを触るのは Filament 管理画面のみのため、管理員スコープで統一している。
 * 一般会員向けフロントを作るときは Member::accessibleSiteIds() を使う別解決が要る。
 *
 * 解決順:
 *   1. session('site_id') がその集合に含まれていれば採用
 *   2. なければ集合の先頭を採用し session に保存
 *   3. 集合が空なら denyAll()（業務データは1件も見えない）
 *
 * 未ログインの場合はここでは何もしない。CurrentSite が必要に応じて
 * session / default_site から解決する。
 */
class ResolveCurrentSite
{
    public function handle(Request $request, Closure $next): Response
    {
        $current = app(CurrentSite::class);
        $user = $request->user();

        if (! $user instanceof Member) {
            return $next($request);
        }

        $accessible = $user->manageableSiteIds()->all();

        if ($accessible === []) {
            $request->session()->forget('site_id');
            $current->denyAll();

            return $next($request);
        }

        $sessionSite = $request->session()->get('site_id');

        if ($sessionSite !== null && in_array($sessionSite, $accessible, true)) {
            $current->set($sessionSite);

            return $next($request);
        }

        $resolved = $accessible[0];
        $current->set($resolved);
        $request->session()->put('site_id', $resolved);

        return $next($request);
    }
}
