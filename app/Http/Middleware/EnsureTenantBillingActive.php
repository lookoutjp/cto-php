<?php

namespace App\Http\Middleware;

use App\Models\Room;
use App\Support\CurrentSite;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 現在のサイト(テナント)が支払い滞納中（Stripe: past_due / unpaid）の場合、
 * 書き込み系リクエスト（GET/HEAD 以外）をブロックする。
 *
 * 閲覧は許可する。フリープラン・未契約は「滞納」ではないので通す
 * （フリープランの上限は会員追加時に別途チェック）。
 * 管理画面(/admin)には掛けない — 支払い方法を更新できる導線を残すため。
 */
class EnsureTenantBillingActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethodSafe()) {
            return $next($request);
        }

        $siteId = app(CurrentSite::class)->idOrNull();

        if ($siteId !== null) {
            $room = Room::find($siteId);

            if ($room && $room->billingIsDelinquent()) {
                abort(
                    Response::HTTP_PAYMENT_REQUIRED,
                    'お支払いが確認できないため、この操作は制限されています。サイト管理者にお問い合わせください。',
                );
            }
        }

        return $next($request);
    }
}
