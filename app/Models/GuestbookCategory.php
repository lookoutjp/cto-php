<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 掲示板のコミュニティ（カテゴリ）。旧ASP: guestbookc / meetlist.asp。
 *
 * id=1 は「サイト掲示板」（全参加者向けの既定カテゴリ）。
 * meetlist.asp はそれ以外（id<>1）をコミュニティ一覧として表示していた。
 */
class GuestbookCategory extends Model
{
    use BelongsToSite;

    /** 既定の「サイト掲示板」カテゴリID。 */
    public const SITE_BOARD_ID = 1;

    protected $table = 'guestbook_categories';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'madetime' => 'datetime',
    ];

    public function threads(): HasMany
    {
        return $this->hasMany(Guestbook::class, 'category')->whereRaw("coalesce(parent, '0') = '0'");
    }

    /** id=1 の既定カテゴリを除外（コミュニティ一覧用）。 */
    public function scopeCommunities(Builder $query): Builder
    {
        return $query->where('id', '<>', self::SITE_BOARD_ID)->orderBy('id');
    }

    public function isSiteBoard(): bool
    {
        return (int) $this->id === self::SITE_BOARD_ID;
    }

    /** 表示名。id=1 は旧ASP同様「サイト掲示板」と表示する。 */
    public function displayName(): string
    {
        return $this->isSiteBoard() ? 'サイト掲示板' : strip_tags((string) $this->name, '<br>');
    }

    /**
     * 参加者を限定しているコミュニティか（旧ASP: guestbookc.member の "||id||id||" リスト）。
     * サイト掲示板(id=1)とリスト空は無制限。
     *
     * @return list<string> 許可された member_id（空 = 無制限）
     */
    public function allowedMemberIds(): array
    {
        if ($this->isSiteBoard()) {
            return [];
        }

        return array_values(array_filter(array_map(
            'trim',
            preg_split('/\|\|/', (string) $this->member) ?: []
        ), fn ($v) => $v !== ''));
    }

    /** この会員がこのコミュニティを閲覧・投稿できるか。管理員・スーパー管理者は常に可。 */
    public function allowsMember(?Member $member): bool
    {
        $allowed = $this->allowedMemberIds();
        if ($allowed === []) {
            return true;
        }

        if (! $member) {
            return false;
        }

        return $member->isSuperAdmin()
            || $member->managesSite((string) $this->site_id)
            || in_array((string) $member->getKey(), $allowed, true);
    }
}
