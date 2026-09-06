<?php

namespace App\Http\Middleware;

use App\Models\Member;
use App\Models\Room;
use App\Support\CurrentSite;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * リクエストごとに CurrentSite を確定させる。
 *
 * 「管理画面(/admin)」と「公開フロント」で対象サイトの決め方が違う:
 *
 *   管理画面 + ログイン中 Member
 *     → 対象は manageableSiteIds()（管理員 = 旧ASP ninshou -1、またはスーパー管理者）
 *     → session('admin_site_id') で切替（SiteSwitcher が書く）
 *     → 管理サイトが無ければ denyAll()
 *
 *   公開フロント + ログイン中 Member
 *     → 対象は accessibleSiteIds()（所属していれば ninshou 問わない）
 *     → session('site_id')（/{site}/ プレフィックスで明示選択）→ ホスト名 → 既定サイト → 先頭
 *       ※ session の値は所属外でも尊重する（公開コンテンツの閲覧のみ。管理・PM機能は別途 gate）
 *
 *   公開フロント + 未ログイン
 *     → session('site_id')（/{site}/ プレフィックスで明示選択）→ ホスト名（rooms.sitedomain）→ 既定サイト
 *
 * `/{site}/…`（SitePageController@enter）に来ると session('site_id') に {site} を保存し
 * ルート無しの URL へリダイレクトする。以降その session が「表示中サイト」を決める。
 *
 * livewire/update は web ミドルウェア経由で管理画面からも来るため、
 * リクエストパスだけでなく Referer も見て管理画面コンテキストか判定する。
 */
class ResolveCurrentSite
{
    public function handle(Request $request, Closure $next): Response
    {
        $current = app(CurrentSite::class);
        $user = $request->user();
        $default = config('app.default_site', 'www');

        // --- ゲスト（公開フロント）---
        if (! $user instanceof Member) {
            $current->set(
                $this->explicitlyChosenSite($request)
                    ?? Room::resolveSiteIdFromHost($request->getHost())
                    ?? $default
            );

            return $next($request);
        }

        // --- 管理画面コンテキスト ---
        if ($this->isAdminContext($request)) {
            $manageable = $user->manageableSiteIds()->all();

            if ($manageable === []) {
                $request->session()->forget('admin_site_id');
                $current->denyAll();

                return $next($request);
            }

            $picked = $request->session()->get('admin_site_id');
            if (! in_array($picked, $manageable, true)) {
                $picked = $manageable[0];
                $request->session()->put('admin_site_id', $picked);
            }
            $current->set($picked);

            return $next($request);
        }

        // --- 公開フロント + ログイン中 Member ---
        $accessible = $user->accessibleSiteIds()->all();

        if ($accessible === []) {
            $current->denyAll();

            return $next($request);
        }

        // /{site}/ プレフィックスで明示選択されたサイトは、所属外でも尊重する
        // （見えるのは公開コンテンツのみ。管理・プロジェクト機能は managesSite() /
        //   isProjectMemberOf() で別途 gate されるので閲覧させて問題ない）。
        $explicit = $this->explicitlyChosenSite($request);
        $fromHost = Room::resolveSiteIdFromHost($request->getHost());
        $fromSession = $request->session()->get('site_id');

        $picked = match (true) {
            $explicit !== null => $explicit,
            in_array($fromHost, $accessible, true) => $fromHost,
            in_array($fromSession, $accessible, true) => $fromSession,
            in_array($default, $accessible, true) => $default,
            default => $accessible[0],
        };

        $current->set($picked);
        $request->session()->put('site_id', $picked);

        return $next($request);
    }

    /**
     * `/{site}/…` プレフィックス（SitePageController@enter）で明示選択されたサイト。
     * 実在するテナントであることだけ確認する（所属チェックはしない＝公開コンテンツ閲覧用）。
     */
    private function explicitlyChosenSite(Request $request): ?string
    {
        $siteId = $request->session()->get('site_view');

        if ($siteId === null || $siteId === '') {
            return null;
        }

        return Room::whereKey($siteId)->exists() ? $siteId : null;
    }

    private function isAdminContext(Request $request): bool
    {
        if ($request->is('admin', 'admin/*')) {
            return true;
        }

        $refererPath = (string) parse_url((string) $request->headers->get('referer'), PHP_URL_PATH);

        return Str::startsWith($refererPath, '/admin');
    }
}
