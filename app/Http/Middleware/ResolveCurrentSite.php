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
 * ログイン中 Member の場合、切り替えられるサイトは accessibleSiteIds() に限る:
 *   - スーパー管理者: 全サイト
 *   - それ以外: member_room で所属しているサイトのみ
 *
 * 解決順:
 *   1. session('site_id') が「アクセス可能」なら採用
 *   2. なければ accessibleSiteIds() の先頭を採用し session に保存
 *   3. アクセス可能なサイトが1つも無ければ denyAll()（業務データは1件も見えない）
 *
 * 未ログイン（フロント）の場合はここでは何もしない。CurrentSite が
 * 必要に応じて session / default_site から解決する。
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

        $accessible = $user->accessibleSiteIds()->all();

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
