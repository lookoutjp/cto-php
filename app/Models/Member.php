<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class Member extends Authenticatable implements FilamentUser
{
    use Notifiable;

    protected $table = 'members';
    protected $primaryKey = 'member_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = [];
    protected $hidden = ['password'];

    /**
     * この会員が所属するサイト(テナント)一覧。
     */
    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class, 'member_room', 'member_id', 'site_id')
            ->withPivot('ninshou');
    }

    /**
     * プラットフォーム運営者（全サイトを管理できる）か。
     * config/app.php の super_admin_member_ids で指定する。
     */
    public function isSuperAdmin(): bool
    {
        return in_array(
            $this->getKey(),
            (array) config('app.super_admin_member_ids', []),
            true
        );
    }

    /**
     * この会員が「所属」している site_id 一覧（ninshou は問わない）。
     * フロント側（一般会員向け画面）のテナント解決に使う想定。
     * スーパー管理者は全サイト。
     */
    public function accessibleSiteIds(): Collection
    {
        if ($this->isSuperAdmin()) {
            return Room::query()->orderBy('site_id')->pluck('site_id');
        }

        return $this->rooms()->orderBy('rooms.site_id')->pluck('rooms.site_id');
    }

    /**
     * この会員が「管理員」(旧ASP ninshou = -1) として管理できる site_id 一覧。
     * 管理画面(/admin)のアクセス可否とサイト切替の候補に使う。
     * スーパー管理者は全サイト。
     */
    public function manageableSiteIds(): Collection
    {
        if ($this->isSuperAdmin()) {
            return Room::query()->orderBy('site_id')->pluck('site_id');
        }

        return $this->rooms()
            ->wherePivot('ninshou', -1)
            ->orderBy('rooms.site_id')
            ->pluck('rooms.site_id');
    }

    /**
     * 指定サイトの管理員か。
     */
    public function managesSite(string $siteId): bool
    {
        return $this->manageableSiteIds()->contains($siteId);
    }

    /**
     * Filament 管理画面へのアクセス可否。
     * 管理員として1サイト以上を管理できる会員、またはスーパー管理者のみ。
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->manageableSiteIds()->isNotEmpty();
    }
}
