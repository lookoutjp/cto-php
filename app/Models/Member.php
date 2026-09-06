<?php

namespace App\Models;

use App\Support\CurrentSite;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

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

    protected $casts = [
        'loginedtime' => 'datetime',
        'timerenew' => 'datetime',
        'pointmtime' => 'datetime',
    ];

    /** オンライン判定の有効時間（分）。旧ASP onlinechk.asp は 20 分。 */
    public const PRESENCE_MINUTES = 15;

    protected static function booted(): void
    {
        // 会員を削除したら、その会員のサイト権限・加入申請（member_room）も消す。
        // 会員のいない member_room 行は意味を持たない孤児レコード。
        static::deleting(function (Member $member): void {
            MemberRoom::query()
                ->withoutGlobalScope('confirmed')
                ->where('member_id', $member->getKey())
                ->delete();
        });
    }

    /** 最終アクセスが直近 PRESENCE_MINUTES 以内ならオンライン。 */
    public function isOnline(): bool
    {
        return $this->timerenew !== null
            && $this->timerenew->greaterThanOrEqualTo(now()->subMinutes(self::PRESENCE_MINUTES));
    }

    /** @param Builder $query */
    public function scopeOnline($query)
    {
        return $query->where('timerenew', '>=', now()->subMinutes(self::PRESENCE_MINUTES));
    }

    /**
     * この会員が所属するサイト(テナント)一覧。
     */
    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class, 'member_room', 'member_id', 'site_id')
            ->withPivot('ninshou');
    }

    /**
     * 表示名（旧ASP: name があれば name、無ければ member_id）。
     */
    public function displayName(): string
    {
        return $this->name ?: $this->member_id;
    }

    /**
     * hp カラムを開ける URL に正規化する。スキームが無ければ // を補う。空なら null。
     */
    public function homepageUrl(): ?string
    {
        $hp = trim((string) $this->hp);
        if ($hp === '') {
            return null;
        }

        return Str::startsWith($hp, ['http://', 'https://']) ? $hp : '//'.$hp;
    }

    /**
     * 性別ラベル（旧ASP membermod.asp: 1=男 / 0=女 / 空=未回答）。
     */
    public function sexLabel(): ?string
    {
        return match ((string) $this->sex) {
            '1' => '男性',
            '0' => '女性',
            default => null,
        };
    }

    /**
     * 指定サイトでの権限レベル（member_room.ninshou）。所属していなければ null。
     */
    public function ninshouOn(string $siteId): ?int
    {
        $room = $this->rooms->firstWhere('site_id', $siteId)
            ?? $this->rooms()->where('rooms.site_id', $siteId)->first();

        return $room?->pivot->ninshou;
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
     * 加入申請中（未承認）のサイトも含む＝承認待ちでも公開コンテンツは閲覧できる。
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
     * 加入申請中（未承認）のサイト site_id 一覧。
     */
    public function pendingSiteIds(): Collection
    {
        return MemberRoom::query()
            ->withoutGlobalScope('confirmed')
            ->pendingRequests()
            ->where('member_id', $this->getKey())
            ->orderBy('site_id')
            ->pluck('site_id');
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
     * 指定サイトの「プロジェクト参加者」か（旧ASP: ninshou = 1 または -1）。
     * TODO / 課題 / リスク / WBS / サーベイ / Mypage など業務系画面の利用条件。
     * ninshou = 0（＝コンテンツ閲覧のみの一般会員）は不可。スーパー管理者は常に可。
     */
    public function isProjectMemberOf(?string $siteId = null): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $siteId ??= app(CurrentSite::class)->idOrNull();
        if ($siteId === null) {
            return false;
        }

        return $this->rooms()
            ->where('rooms.site_id', $siteId)
            ->wherePivotIn('ninshou', [1, -1])
            ->exists();
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
