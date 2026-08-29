<?php

namespace App\Http\Middleware;

use App\Models\Member;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 業務系画面（TODO / 課題 / リスク / WBS / サーベイ）の利用条件:
 * 現在のサイトの「プロジェクト参加者」（旧ASP ninshou = 1 または -1）であること。
 *
 * ninshou = 0（コンテンツ閲覧のみの会員）や、現在のサイトに所属していない
 * ログインユーザーは 403。
 */
class EnsureProjectMember
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof Member || ! $user->isProjectMemberOf()) {
            abort(403, 'このサイトのプロジェクト機能をご利用いただく権限がありません。');
        }

        return $next($request);
    }
}
