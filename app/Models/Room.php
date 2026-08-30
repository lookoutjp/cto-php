<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Cashier\Billable;

class Room extends Model
{
    use Billable;

    protected $table = 'rooms';
    protected $primaryKey = 'site_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'trial_ends_at' => 'datetime',
    ];

    /**
     * Cashier の顧客名（Stripe ダッシュボード表示用）。
     */
    public function stripeName(): ?string
    {
        return $this->sitename ?: $this->site_id;
    }

    public function stripeEmail(): ?string
    {
        return $this->site_mail ?: $this->comemail;
    }

    /**
     * HTTP ホスト名からサイト(テナント)を引く。旧ASP conn.asp の
     * 「HTTP_HOST からドメインを引いて siteid を決める」に相当。
     * 該当なしなら null（呼び出し側で config('app.default_site') に fallback）。
     */
    public static function resolveSiteIdFromHost(?string $host): ?string
    {
        if (blank($host)) {
            return null;
        }

        $host = strtolower(preg_replace('/:\d+$/', '', $host));
        $bare = preg_replace('/^www\./', '', $host);

        return static::query()
            ->get(['site_id', 'sitedomain'])
            ->first(function (Room $room) use ($host, $bare) {
                $domain = strtolower((string) parse_url((string) $room->sitedomain, PHP_URL_HOST));
                if ($domain === '') {
                    return false;
                }
                $domain = preg_replace('/^www\./', '', $domain);

                return $domain === $host || $domain === $bare;
            })?->site_id;
    }

    /**
     * サイトで機能フラグが有効か（旧ASP checkfunction_F / rooms.function_list）。
     * function_list は "flagA,flagB,flagC," のようなカンマ区切り文字列。
     */
    public function hasFunction(string $flag): bool
    {
        $list = ','.str_replace(' ', '', (string) $this->function_list).',';

        return str_contains($list, ','.$flag.',');
    }

    /**
     * このサイト(テナント)に所属する会員一覧。
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Member::class, 'member_room', 'site_id', 'member_id')
            ->withPivot('ninshou');
    }

    // ---- 課金 ----

    /** 現在のプランキー（free / standard / pro）。 */
    public function planKey(): string
    {
        return \App\Support\Plans::keyForRoom($this);
    }

    /** 現在のプラン定義（config/plans.php、'key' 付き）。 */
    public function plan(): array
    {
        return \App\Support\Plans::forRoom($this);
    }

    public function planLimit(string $key): ?int
    {
        return \App\Support\Plans::limit($this, $key);
    }

    public function memberCount(): int
    {
        return \App\Support\Plans::memberUsage($this);
    }

    /** あと $n 人追加してもプランの会員上限内か。 */
    public function canAddMembers(int $n = 1): bool
    {
        return \App\Support\Plans::withinMemberLimit($this, $n);
    }

    /** 支払い滞納中（past_due / unpaid）か。 */
    public function billingIsDelinquent(): bool
    {
        return \App\Support\Plans::isDelinquent($this);
    }

    /** 有料プラン契約中（トライアル・猶予期間を含む）か。 */
    public function onPaidPlan(): bool
    {
        return $this->planKey() !== 'free';
    }
}
