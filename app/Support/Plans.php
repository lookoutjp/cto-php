<?php

namespace App\Support;

use App\Models\Room;
use Illuminate\Support\Facades\DB;

/**
 * 料金プラン（config/plans.php）の参照と、テナント(Room)の現在プラン・使用量の判定。
 *
 * 「現在のプラン」= アクティブなサブスクリプションの stripe_price に対応するプラン。
 * サブスクが無い / price が対応しない → free。
 */
class Plans
{
    public const DEFAULT_SUBSCRIPTION = 'default';

    /** @return array<string, array> */
    public static function all(): array
    {
        return config('plans', []);
    }

    public static function get(string $key): ?array
    {
        $plan = config("plans.$key");

        return $plan ? ['key' => $key] + $plan : null;
    }

    /** 契約可能な有料プラン（stripe_price_id が設定されているもの）。 */
    public static function purchasable(): array
    {
        return array_filter(
            self::all(),
            fn ($plan) => ! empty($plan['stripe_price_id']),
        );
    }

    /** stripe_price（Price ID）からプランキーを引く。 */
    public static function keyForStripePrice(?string $stripePrice): ?string
    {
        if (! $stripePrice) {
            return null;
        }

        foreach (self::all() as $key => $plan) {
            if (($plan['stripe_price_id'] ?? null) === $stripePrice) {
                return $key;
            }
        }

        return null;
    }

    public static function keyForRoom(Room $room): string
    {
        $subscription = $room->subscription(self::DEFAULT_SUBSCRIPTION);

        if ($subscription && $subscription->valid()) {
            return self::keyForStripePrice($subscription->stripe_price) ?? 'free';
        }

        return 'free';
    }

    /** @return array プランキー付きのプラン定義 */
    public static function forRoom(Room $room): array
    {
        return self::get(self::keyForRoom($room)) ?? self::get('free');
    }

    /** limits の値（整数 = 上限 / null = 無制限）。 */
    public static function limit(Room $room, string $limitKey): ?int
    {
        return self::forRoom($room)['limits'][$limitKey] ?? null;
    }

    /** そのテナントの会員数（member_room の総数）。 */
    public static function memberUsage(Room $room): int
    {
        return DB::table('member_room')->where('site_id', $room->getKey())->count();
    }

    /** あと $additional 人追加してもプランの会員上限内か。 */
    public static function withinMemberLimit(Room $room, int $additional = 0): bool
    {
        $limit = self::limit($room, 'members');

        if ($limit === null) {
            return true;
        }

        return self::memberUsage($room) + $additional <= $limit;
    }

    /** そのテナントのストレージ使用量（bytes）。files ＋ attachments の size_bytes 合計。 */
    public static function storageUsageBytes(Room $room): int
    {
        $siteId = $room->getKey();

        return (int) DB::table('files')->where('site_id', $siteId)->sum('size_bytes')
            + (int) DB::table('attachments')->where('site_id', $siteId)->sum('size_bytes');
    }

    /** あと $additionalBytes バイト追加してもプランのストレージ上限内か。 */
    public static function withinStorageLimit(Room $room, int $additionalBytes = 0): bool
    {
        $limitMb = self::limit($room, 'storage_mb');

        if ($limitMb === null) {
            return true;
        }

        return self::storageUsageBytes($room) + $additionalBytes <= $limitMb * 1024 * 1024;
    }

    /**
     * 支払い滞納状態か（past_due / unpaid）。
     * 解約後の猶予期間切れは「free に戻った」だけで滞納ではない。
     */
    public static function isDelinquent(Room $room): bool
    {
        $subscription = $room->subscription(self::DEFAULT_SUBSCRIPTION);

        return $subscription !== null
            && in_array($subscription->stripe_status, ['past_due', 'unpaid'], true);
    }
}
