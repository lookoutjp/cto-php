<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class Member extends Authenticatable
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
     * プラットフォーム運営者（全サイトに切り替え可能）か。
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
     * この会員がアクセス（サイト切替）できる site_id の一覧。
     *   - スーパー管理者: 全サイト（rooms 全件）
     *   - それ以外: member_room で所属しているサイトのみ
     */
    public function accessibleSiteIds(): Collection
    {
        if ($this->isSuperAdmin()) {
            return Room::query()->orderBy('site_id')->pluck('site_id');
        }

        return $this->rooms()->orderBy('site_id')->pluck('rooms.site_id');
    }

    /**
     * 指定サイトの管理員（旧ASP ninshou = -1）か。
     */
    public function managesSite(string $siteId): bool
    {
        return $this->isSuperAdmin()
            || $this->rooms()
                ->wherePivot('ninshou', -1)
                ->where('rooms.site_id', $siteId)
                ->exists();
    }
}
