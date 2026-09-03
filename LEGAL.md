# 法務ページ（特商法 / 利用規約 / プライバシーポリシー）

有料サービス（サブスクリプション）を提供するうえで必要な、サービス共通（テナント非依存）の
法務ページ。運営者＝プラットフォーム事業者の情報を表示する。

| URL | ルート名 | 内容 |
|---|---|---|
| `/legal/tokushoho` | `legal.tokushoho` | 特定商取引法に基づく表記。事業者情報＋`config/plans.php` の価格表＋支払・解約条件 |
| `/legal/terms` | `legal.terms` | 利用規約（13条のひな型） |
| `/legal/privacy` | `legal.privacy` | プライバシーポリシー（取得情報・利用目的・委託先: Stripe/R2/Resend/Neon/Laravel Cloud） |

- 実装: `App\Http\Controllers\LegalController` + `resources/views/legal/*`
- レイアウトは公開フロントと同じ `x-layouts.public`
- フッター（`components/layouts/public.blade.php`）と `/admin/billing` の申込ボタン近くにリンク
- `resources/views/legal/partials/footer-links.blade.php` = 3ページ相互リンク

## 設定（`config/legal.php` ← `.env`）

```
LEGAL_SERVICE_NAME="CtoS"
LEGAL_OPERATOR_NAME=        # 事業者名（法人名 or 屋号）
LEGAL_REPRESENTATIVE=       # 運営統括責任者
LEGAL_ADDRESS=              # 所在地
LEGAL_PHONE=                # 電話番号
LEGAL_PHONE_HOURS="平日 10:00〜17:00（土日祝を除く）"
LEGAL_EMAIL=                # 未設定なら MAIL_FROM_ADDRESS を使う
LEGAL_JURISDICTION="東京地方裁判所"
LEGAL_TERMS_UPDATED=2026-09-02
LEGAL_PRIVACY_UPDATED=2026-09-02
LEGAL_TOKUSHOHO_UPDATED=2026-09-02
```

- 未設定の項目はページ上に「—（未設定）」と表示され、**スーパー管理者にのみ**警告バナーが出る。
- 本番稼働前に Laravel Cloud の Custom environment variables に `LEGAL_*` を設定すること。

## 注意

- 利用規約・プライバシーポリシーの本文は**ひな型**。内容は事業形態に合わせて弁護士等のレビューを推奨。
- 個人事業主で住所・電話を公開したくない場合、特商法上は「請求があれば遅滞なく開示する」旨の記載で
  省略できるケースがある（要確認）。その場合は `LEGAL_ADDRESS` / `LEGAL_PHONE` にその旨を記載する。
- 特商法表記は「申込みの直前に確認できる場所」からリンクされている必要がある → `/admin/billing` に設置済み。
- 価格は税込表示（`config/plans.php` の `price` を税込額にしておく）。
