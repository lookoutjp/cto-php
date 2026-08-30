# 課金設計（有償SaaS化）

`laravel/cashier`（Stripe）でテナント単位のサブスクリプション課金を行う。
**課金方式は「月額フラット」**（プランごとに固定額 + 会員数・容量の上限）。
**フリープランあり**（フリーミアム）。通貨は JPY。

## 課金の単位: テナント（`Room` / site_id）

- 契約者はサイト（テナント）であって個々の会員ではない。
- Cashier の顧客モデルは `App\Models\Room`（`AppServiceProvider::boot` で `Cashier::useCustomerModel`）。
- 主キーが文字列 `site_id` のため、サブスク所有者カラムは `subscriptions.room_site_id`（`create_subscriptions_table` を書き換え済み）。
- 支払い操作ができるのは、そのサイトの管理員（`member_room.ninshou = -1`）またはスーパー管理者。

## 実装済み

| 要素 | 内容 |
|---|---|
| `composer` | `laravel/cashier` ^16 |
| マイグレーション | `create_customer_columns` → `rooms` に `stripe_id`/`pm_type`/`pm_last_four`/`trial_ends_at`。`create_subscriptions_table` → 所有者を `room_site_id`(string) に。`subscription_items` は変更なし |
| `App\Models\Room` | `use Billable`、`stripeName()`=`sitename`、`stripeEmail()`=`site_mail`。課金ヘルパ: `planKey()` / `plan()` / `planLimit()` / `memberCount()` / `canAddMembers()` / `billingIsDelinquent()` / `onPaidPlan()` |
| `config/plans.php` | `free`（会員10・200MB・¥0）/ `standard`（会員50・5GB・¥3,000）/ `pro`（無制限・50GB・¥10,000）。`stripe_price_id` は `env('STRIPE_PRICE_*')` |
| `App\Support\Plans` | プラン参照とテナントの現在プラン判定。`keyForRoom()`（有効なサブスクの `stripe_price` → プラン、無ければ `free`）/ `memberUsage()` / `withinMemberLimit()` / `isDelinquent()`（`past_due`/`unpaid`） |
| `App\Filament\Pages\Billing` | `/admin/billing`「プラン・お支払い」。現在プラン・使用状況・プラン一覧・契約/変更(`swap`)/解約(`cancel`)/再開(`resume`)/Billing Portal。`canAccess()` は現在サイトの管理員のみ。Stripe 未設定時は使用状況の確認のみ（操作は警告表示で無効） |
| `App\Http\Middleware\EnsureTenantBillingActive` | 支払い滞納中テナントの**書き込み系（非GET）をブロック**（閲覧は可）。`routes/web.php` の業務系グループ（`EnsureProjectMember` と併用）に適用。`/admin` には掛けない（支払い導線を残すため） |
| 会員数上限 | `RegisteredUserController::store` で自己登録時に `Room::canAddMembers(1)` をチェック。上限到達サイトは登録を弾く |
| `bootstrap/app.php` | `stripe/*` を CSRF 検証から除外（Webhook 用） |
| `.env` / `.env.example` | `STRIPE_KEY` / `STRIPE_SECRET` / `STRIPE_WEBHOOK_SECRET` / `CASHIER_CURRENCY=jpy` / `CASHIER_CURRENCY_LOCALE=ja` / `STRIPE_PRICE_STANDARD` / `STRIPE_PRICE_PRO`（すべて空 = 全テナント free 扱い） |

Stripe キー未設定でもアプリは通常どおり動作する（`Plans::purchasable()` が空 → 契約操作は不可、全テナント free）。

## Stripe テストモードでの疎通確認（2026-08-30 完了）

- Stripe アカウント作成済み。テストキーを `.env` に設定済み（`STRIPE_KEY` / `STRIPE_SECRET`）。
- Product / Price はダッシュボードでなく API で作成した → `schema-gen`... ではなく手元スクリプトで
  `plan_key` メタデータ付き。`STRIPE_PRICE_STANDARD` / `STRIPE_PRICE_PRO` に設定済み（¥3,000 / ¥10,000、JPY 月次）。
  再作成が要る場合は `metadata['plan_key']` で既存を検索して再利用する冪等スクリプトを書けばよい。
- Webhook: Stripe CLI（`winget install Stripe.StripeCli`）。ローカルは
  `stripe listen --forward-to localhost:8010/stripe/webhook --api-key sk_test_...`。
  署名シークレットは `stripe listen --print-secret` で取得し `STRIPE_WEBHOOK_SECRET` に設定済み
  （このシークレットはアカウント固定なので毎回同じ）。
- **通しで確認済み**（demo テナント、テスト PaymentMethod `pm_card_visa`）:
  Checkout 画面への遷移 → サブスク作成でスタンダード反映（カード `•••• 4242`）→
  `swap` standard→pro → `cancel`（猶予期間 UI・`ends_at` 表示）→ `resume` →
  滞納（`stripe_status = past_due`）で業務系の書き込みが 402・GET は 200。
  Webhook（`customer.subscription.*` / `invoice.*` 等）はすべて 200 で処理。
- 確認後、demo は `cancelNow()` で free 状態に戻してある。

### 本番移行時にやること

1. 本番キー（`sk_live_` / `pk_live_`）に差し替え、本番ダッシュボードで JPY 月次 Price を作成
2. 本番は `php artisan cashier:webhook` で Stripe 側にエンドポイント登録 → 署名シークレットを `.env` に
3. `trial` を入れるなら `config/plans.php` に `trial_days`、`newSubscription()->trialDays()` を追加

## まだ手を付けていない設計判断

| 項目 | メモ |
|---|---|
| トライアル | 現状なし。付けるなら `trial_ends_at` ベースで N 日 |
| 管理画面側の会員数上限 | 自己登録のみチェック済み。Filament の `MemberRoomResource` 追加や `ninshou` 引き上げ時のチェックは未（`MemberRoom::creating` で `Room::canAddMembers()` を見るのが素直。ただし Filament で例外を投げると 500 になるのでフォームバリデーションで） |
| ストレージ従量 | **月額フラット内の上限として実装済み**（`Plans::storageUsageBytes()` = `files.size_bytes` 合計、アップロード時 `withinStorageLimit()` でブロック、`/admin/billing` に使用量表示）。使った分だけ課金する従量制にするかは別途 |
| 未契約テナントのグレース | 現状フリープランがあるので「未契約 = free」。free の上限超過分（会員数）は自己登録ブロックのみ。既存データの読み取りは常に許可 |
| 請求書 PDF | Cashier の `DompdfInvoiceRenderer` が既定。日本語フォント埋め込みは要確認 |

## 関連

- スーパー管理者（`APP_SUPER_ADMIN_MEMBER_IDS`）は課金の影響を受けない運用者アカウント（`Plans` は `manageableSiteIds` 経由で全サイト管理可能だが、サブスク自体はサイト単位）。
- マルチテナントの権限モデルは `MULTITENANCY.md`。
