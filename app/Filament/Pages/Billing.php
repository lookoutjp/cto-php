<?php

namespace App\Filament\Pages;

use App\Models\Member;
use App\Models\Room;
use App\Support\CurrentSite;
use App\Support\Plans;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

/**
 * テナント（現在切替中のサイト）のプラン・お支払い画面。
 *
 * Stripe 未設定（STRIPE_SECRET / Price ID 空）でも表示でき、その場合は
 * 現在の使用状況の確認のみ・契約操作は不可という状態を明示する。
 */
class Billing extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'アカウント';

    protected static ?int $navigationSort = 90;

    protected static string $view = 'filament.pages.billing';

    public function getTitle(): string|Htmlable
    {
        return 'プラン・お支払い';
    }

    public static function getNavigationLabel(): string
    {
        return 'プラン・お支払い';
    }

    /** そのサイトの管理員（またはスーパー管理者）のみ。 */
    public static function canAccess(): bool
    {
        $user = auth()->user();
        $siteId = app(CurrentSite::class)->idOrNull();

        return $user instanceof Member
            && $siteId !== null
            && $user->managesSite($siteId);
    }

    public function mount(): void
    {
        $checkout = request()->query('checkout');

        if ($checkout === 'success') {
            Notification::make()
                ->success()
                ->title('お支払いを受け付けました')
                ->body('反映まで数秒かかることがあります。')
                ->send();
        } elseif ($checkout === 'cancel') {
            Notification::make()->warning()->title('お支払いをキャンセルしました')->send();
        }
    }

    protected function room(): Room
    {
        return Room::findOrFail(app(CurrentSite::class)->id());
    }

    protected function stripeConfigured(): bool
    {
        return filled(config('cashier.secret'));
    }

    // ---- 表示データ ----

    protected function getViewData(): array
    {
        $room = $this->room();
        $subscription = $room->subscription(Plans::DEFAULT_SUBSCRIPTION);

        $renewsAt = null;
        if ($this->stripeConfigured() && $subscription && $subscription->active() && ! $subscription->onGracePeriod()) {
            try {
                $stripeSub = $subscription->asStripeSubscription(['items']);
                // 現行の Stripe API では current_period_end はサブスクリプションアイテム側にある
                $ts = $stripeSub->current_period_end
                    ?? $stripeSub->items->data[0]->current_period_end
                    ?? null;
                $renewsAt = $ts ? \Illuminate\Support\Carbon::createFromTimestamp($ts) : null;
            } catch (\Throwable $e) {
                $renewsAt = null;
            }
        }

        return [
            'room' => $room,
            'currentPlan' => $room->plan(),
            'plans' => Plans::all(),
            'purchasable' => Plans::purchasable(),
            'stripeConfigured' => $this->stripeConfigured(),
            'memberUsage' => $room->memberCount(),
            'memberLimit' => $room->planLimit('members'),
            'storageUsedBytes' => Plans::storageUsageBytes($room),
            'storageLimit' => $room->planLimit('storage_mb'),
            'subscription' => $subscription,
            'onGracePeriod' => $subscription?->onGracePeriod() ?? false,
            'delinquent' => $room->billingIsDelinquent(),
            'renewsAt' => $renewsAt,
        ];
    }

    // ---- 契約操作 ----

    public function subscribe(string $planKey): mixed
    {
        if (! $this->guardStripe()) {
            return null;
        }

        $plan = Plans::get($planKey);
        if (! $plan || empty($plan['stripe_price_id'])) {
            Notification::make()->danger()->title('このプランは選択できません')->send();

            return null;
        }

        $checkout = $this->room()
            ->newSubscription(Plans::DEFAULT_SUBSCRIPTION, $plan['stripe_price_id'])
            ->checkout([
                'success_url' => static::getUrl().'?checkout=success',
                'cancel_url' => static::getUrl().'?checkout=cancel',
            ]);

        return redirect($checkout->url);
    }

    public function swap(string $planKey): void
    {
        if (! $this->guardStripe()) {
            return;
        }

        $plan = Plans::get($planKey);
        $subscription = $this->room()->subscription(Plans::DEFAULT_SUBSCRIPTION);

        if (! $subscription || ! $plan || empty($plan['stripe_price_id'])) {
            Notification::make()->danger()->title('プランを変更できません')->send();

            return;
        }

        $subscription->swap($plan['stripe_price_id']);

        Notification::make()->success()->title("プランを「{$plan['name']}」に変更しました")->send();
    }

    public function cancel(): void
    {
        if (! $this->guardStripe()) {
            return;
        }

        $subscription = $this->room()->subscription(Plans::DEFAULT_SUBSCRIPTION);
        $subscription?->cancel();

        Notification::make()
            ->success()
            ->title('解約を受け付けました')
            ->body('現在の請求期間の終わりまではご利用いただけます。')
            ->send();
    }

    public function resume(): void
    {
        if (! $this->guardStripe()) {
            return;
        }

        $subscription = $this->room()->subscription(Plans::DEFAULT_SUBSCRIPTION);

        if ($subscription && $subscription->onGracePeriod()) {
            $subscription->resume();
            Notification::make()->success()->title('契約を再開しました')->send();
        }
    }

    public function billingPortal(): mixed
    {
        if (! $this->guardStripe()) {
            return null;
        }

        $room = $this->room();

        if (! $room->hasStripeId()) {
            Notification::make()->warning()->title('まだ Stripe 顧客が作成されていません')->send();

            return null;
        }

        return redirect($room->billingPortalUrl(static::getUrl()));
    }

    protected function guardStripe(): bool
    {
        if (! $this->stripeConfigured()) {
            Notification::make()
                ->warning()
                ->title('Stripe が未設定です')
                ->body('STRIPE_SECRET と各プランの Price ID を .env に設定してください。')
                ->send();

            return false;
        }

        return true;
    }
}
