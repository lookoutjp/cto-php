<?php

namespace App\Support;

use App\Models\Member;
use Illuminate\Support\Facades\Auth;

/**
 * 旧ASPの「管理員モード」相当。サイト管理員がONにしている間だけ、公開ページに
 * インライン管理コントロール（追加・編集アイコン）を表示する。
 *
 * サイト（テナント）ごとにON/OFFを持つ（session）。管理員かどうかの判定は
 * isEnabled() では行わない（呼び出し側で Member::managesSite() を必ず確認すること）。
 * ビュー／コントローラからは activeFor() を使うと権限チェック込みで判定できる。
 */
class AdminMode
{
    private const SESSION_KEY = 'admin_mode_sites';

    /**
     * 現在ログイン中の会員がこのサイトの管理員で、かつ管理者モードがONか。
     * 公開フロントで「インライン管理の導線を出すか」の唯一の判定点。
     */
    public static function activeFor(?string $siteId): bool
    {
        if ($siteId === null || $siteId === '') {
            return false;
        }

        $user = Auth::user();

        return $user instanceof Member
            && $user->managesSite($siteId)
            && self::isEnabled($siteId);
    }

    public static function isEnabled(?string $siteId): bool
    {
        if ($siteId === null || $siteId === '') {
            return false;
        }

        return in_array($siteId, session(self::SESSION_KEY, []), true);
    }

    public static function enable(string $siteId): void
    {
        $sites = session(self::SESSION_KEY, []);
        if (! in_array($siteId, $sites, true)) {
            $sites[] = $siteId;
        }
        session([self::SESSION_KEY => $sites]);
    }

    public static function disable(string $siteId): void
    {
        $sites = array_values(array_diff(session(self::SESSION_KEY, []), [$siteId]));
        session([self::SESSION_KEY => $sites]);
    }

    /** @return bool 切替後の状態（true=ON） */
    public static function toggle(string $siteId): bool
    {
        if (self::isEnabled($siteId)) {
            self::disable($siteId);

            return false;
        }

        self::enable($siteId);

        return true;
    }
}
