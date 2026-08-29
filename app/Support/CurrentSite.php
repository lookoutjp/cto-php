<?php

namespace App\Support;

use App\Models\Member;

/**
 * 現在アクティブなサイト(テナント)を表す。旧ASPの Session("yhl.SiteID") 相当。
 *
 * 解決順:
 *   1. 明示的に set() された値（Filamentのサイト切替、コンソールコマンド等）
 *   2. session('site_id')
 *   3. ログイン中 Member が所属する最初のサイト
 *   4. どれも無ければ null（= 未確定。BelongsToSite のスコープは無効化される）
 *
 * id() は fallback として config('app.default_site') を返すため、
 * 「新規レコード作成時に site_id を必ず埋める」用途に使う。
 */
class CurrentSite
{
    private ?string $siteId = null;

    private bool $resolved = false;

    public function set(?string $siteId): void
    {
        $this->siteId = $siteId !== null && $siteId !== '' ? $siteId : null;
        $this->resolved = true;
    }

    public function forget(): void
    {
        $this->siteId = null;
        $this->resolved = false;
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
            $this->siteId = $user->rooms()->first()?->site_id;
        }
    }
}
