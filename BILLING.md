# 課金設計（有償SaaS化） — 未実装・設計メモ

`laravel/cashier`（Stripe）でテナント単位のサブスクリプション課金を行う想定。
実装には Stripe アカウントと価格（Price）ID、料金プランの決定が必要なため、
ここでは方針だけを固めておく。

## 課金の単位: テナント（`Room` / site_id）

- 契約者はサイト（テナント）であって個々の会員ではない。
- 課金対象モデルは `App\Models\Room`（主キー `site_id` の文字列）。
- 支払い者＝そのサイトの管理員（`member_room.ninshou = -1`）のいずれか。

## 実装ステップ（Stripe 準備ができてから）

1. `composer require laravel/cashier`
2. **公開マイグレーションを Room 用に書き換える**
   - `create_customer_columns`: `users` → `rooms`（`stripe_id` / `pm_type` / `pm_last_four` / `trial_ends_at`）
   - `subscriptions` / `subscription_items`: 所有者列を `user_id`(bigint) → `room_site_id`(string, `rooms.site_id` 参照) に変更
3. `App\Models\Room` に `Laravel\Cashier\Billable` を use。
   - `Cashier::useCustomerModel(Room::class)` を `AppServiceProvider::boot()` で設定
   - 文字列キーのため `Billable` の `stripeName()` / owner リレーションのキー指定を確認
4. `config/cashier.php`: `key` / `secret` / `webhook.secret`、`currency = 'jpy'`
5. `.env`: `STRIPE_KEY` / `STRIPE_SECRET` / `STRIPE_WEBHOOK_SECRET` / `CASHIER_CURRENCY=jpy`
6. Webhook ルート（`cashier` が提供）を `routes/web.php` の CSRF 除外に登録
   （`bootstrap/app.php` の `validateCsrfTokens(except: ['stripe/*'])`）

## 画面

- `/admin`（Filament）にテナント管理者向けの「プラン・お支払い」ページ
  - 現在のプラン / 次回請求日 / カード情報（`pm_last_four`）
  - プラン変更・解約（`$room->subscription('default')->swap($priceId)` / `->cancel()`）
  - 支払い方法の更新（Stripe Billing Portal へのリダイレクトが簡単: `$room->redirectToBillingPortal()`）
- 未契約 / 支払い遅延のテナントの扱い
  - `Room` に `onGracePeriod()` / `subscribed()` を見るミドルウェアを web グループに追加し、
    未契約なら閲覧は許可・書き込み系（タスク作成等）をブロック、等のポリシーを決める

## 料金プラン（要決定）

| 決めること | 選択肢 |
|---|---|
| 課金方式 | 月額フラット / 会員数（seat）従量 / ストレージ量従量 |
| 無料枠 | フリープラン（会員 N 名・容量 X まで）を残すか |
| トライアル | `trial_ends_at` で N 日間 |
| 通貨 | JPY（`CASHIER_CURRENCY=jpy`、Stripe 側の Price も JPY で作成） |

## 関連

- ファイルストレージ（`files` テーブル）の S3/R2 化は容量従量課金の前提。`FRONTEND.md` の未実装参照。
- スーパー管理者（`APP_SUPER_ADMIN_MEMBER_IDS`）は課金の影響を受けない運用者アカウント。
