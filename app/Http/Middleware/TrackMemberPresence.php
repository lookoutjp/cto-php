<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * ログイン中の会員の「最終アクセス時刻」を更新する（旧ASP: onlinechk.asp）。
 * 毎リクエスト DB を叩かないよう会員ごとに 60 秒キャッシュでスロットルする。
 * オンライン判定は Member::isOnline()（timerenew が直近 15 分以内）で行う。
 */
class TrackMemberPresence
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $id = $request->user()?->getAuthIdentifier();

        if ($id !== null && ! $request->isMethod('OPTIONS')) {
            $seen = Cache::add('presence:'.$id, true, now()->addSeconds(60));

            if ($seen) {
                DB::table('members')
                    ->where('member_id', $id)
                    ->update(['timerenew' => now(), 'online' => 1]);
            }
        }

        return $response;
    }
}
