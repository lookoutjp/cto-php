<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Support\AdminMode;
use App\Support\CurrentSite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * 旧ASPの「管理員モードに入る／抜ける」トグル。サイト管理員のみ切替可能。
 */
class AdminModeController extends Controller
{
    public function toggle(Request $request, CurrentSite $currentSite): RedirectResponse
    {
        $siteId = $currentSite->idOrNull();
        $user = $request->user();

        abort_unless(
            $siteId !== null && $user instanceof Member && $user->managesSite($siteId),
            403
        );

        AdminMode::toggle($siteId);

        return back();
    }
}
