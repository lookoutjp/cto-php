<?php

namespace App\Support;

use App\Models\Member;

/**
 * 現在アクティブなサイト(テナント)を表す。旧ASPの Session("yhl.SiteID") 相当。
 *
 * 通常は App\Http\Middleware\ResolveCurrentSite がリクエスト開始時に set() する。
 * 未確定のまま参照された場合のフォールバック解決順:
 *   1. session('site_id')
 *   2. ログイン中 Member がアクセス可能な最初のサイト
 *   3. どれも無ければ null（= 未確定。BelongsToSite のスコープは無効）
 *
 * denyAll() 状態のときは、確定サイトの有無に関わらず BelongsToSite が
 * 「1件も返さない」条件を付ける（所属サイトの無いログインユーザー向けの安全側デフォルト）。
 *
 * id() は fallback として config('app.default_site') を返すため、
 * 「新規レコード作成時に site_id を必ず埋める」用途に使う。
 */
class CurrentSite
{
    private ?string $siteId = null;

    private bool $resolved = false;

    private bool $denyAll = false;

    public function set(?string $siteId): void
    {
        $this->siteId = $siteId !== null && $siteId !== '' ? $siteId : null;
        $this->resolved = true;
        $this->denyAll = false;
    }

    public function forget(): void
    {
        $this->siteId = null;
        $this->resolved = false;
        $this->denyAll = false;
    }

    /**
     * アクセス可能なサイトが1つも無いログインユーザー向け。
     * 以降 BelongsToSite のクエリは常に空を返す。
     */
    public function denyAll(): void
    {
        $this->siteId = null;
        $this->resolved = true;
        $this->denyAll = true;
    }

    public function deniesAll(): bool
    {
        if (! $this->resolved) {
            $this->resolve();
        }

        return $this->denyAll;
    }

    /**
     * 確定済みの site_id。未確定なら null。
     * グローバルスコープの絞り込み条件に使う（null のときは絞り込まない）。
     */
    public function idOrNull(): ?string
    {
        if (! $this->resolved) {
            $this->resolve();
        }

        return $this->siteId;
    }

    /**
     * 必ず何らかの site_id を返す（fallback は config('app.default_site')）。
     * レコード作成時の site_id 自動セットに使う。
     */
    public function id(): string
    {
        return $this->idOrNull() ?? config('app.default_site', 'www');
    }

    public function has(): bool
    {
        return $this->idOrNull() !== null;
    }

    private function resolve(): void
    {
        $this->resolved = true;

        if (function_exists('session') && app()->bound('session') && session()->has('site_id')) {
            $this->siteId = (string) session('site_id') ?: null;

            return;
        }

        $user = auth()->user();
        if ($user instanceof Member) {
            $this->siteId = $user->accessibleSiteIds()->first();
        }
    }
}
