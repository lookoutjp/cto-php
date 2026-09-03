# 本番デプロイ手順

構成:

| 層 | サービス | 備考 |
|---|---|---|
| DB | **Neon**（サーバーレス PostgreSQL） | 無料枠でも可。SNI 判定のため libpq 14+ が必須 |
| ファイル実体 | **Cloudflare R2**（S3 互換） | 会員ファイルライブラリ・添付の保存先（`STORAGE.md`） |
| アプリ | **Laravel Cloud**（推奨）または Laravel Forge + VPS | PHP 8.3 / Node 20 |
| 課金 | Stripe（本番キー） | `BILLING.md` |
| メール | Resend（Tokyo region、`cto.jp` Verified） | `MAIL_MAILER=resend` / `RESEND_API_KEY` / `noreply@cto.jp` |

> 前提: このリポジトリを **GitHub にプッシュ済み**であること（下記「0.」）。

---

## 0. GitHub リポジトリ

ローカルの `cto-php` は `git init` 済み・リモート未設定。GitHub で private リポジトリを作り:

```bash
git remote add origin git@github.com:<you>/cto-php.git
git push -u origin main
```

push 後、`.github/workflows/ci.yml`（PostgreSQL サービスでテスト + ビルド）が走る。

---

## 1. Neon（本番 DB）

1. https://neon.tech でプロジェクト作成（リージョンは利用者に近い所）。
2. **接続文字列を2つ**控える（ダッシュボードの Connection Details）:
   - **Pooled**（ホストに `-pooler`）… アプリ実行時に使う
   - **Direct**（`-pooler` なし）… `migrate` / データ投入時に使う
   形式: `postgresql://USER:PASS@ep-xxxx[-pooler].REGION.aws.neon.tech/DBNAME?sslmode=require`

### 1a. ローカルから Neon への初期セットアップ

`.env` に **direct** を入れて実行:

```env
NEON_DATABASE_URL=postgresql://USER:PASS@ep-xxxx.REGION.aws.neon.tech/DBNAME?sslmode=require
```

```bash
php artisan db:neon-check              # 接続・バージョン確認
php artisan db:neon-check --migrate    # Neon へ migrate（direct 接続で）
php schema-gen/load_data.php <CSVルート> neon   # 旧 www データを投入（初回のみ）
```

> **libpq**: Neon は SNI でエンドポイントを判定するため、古い libpq（XAMPP 同梱の 11 系など）だと
> `Endpoint ID is not specified` になる。PostgreSQL 14+ の `libpq.dll` とその依存 DLL を PHP フォルダに
> 置いて対応する（ローカルは PostgreSQL 16 のものを使用）。Laravel Cloud / Forge の Linux 環境では問題なし。

### 1b. ファイル実体の投入（初回のみ）

R2 を `.env` に設定した状態で（Windows、旧サーバの `files/` にアクセスできる環境）:

```powershell
php schema-gen\migrate_legacy_files.php www --dry   # プレビュー
php schema-gen\migrate_legacy_files.php www          # R2 へアップロード
```

---

## 2. Cloudflare R2

`STORAGE.md` の手順でバケット `cto-php` と API トークンを作成。`.env`（本番）に:

```env
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=auto
AWS_BUCKET=cto-php
AWS_ENDPOINT=https://<accountid>.r2.cloudflarestorage.com
AWS_USE_PATH_STYLE_ENDPOINT=true
```

---

## 3. Laravel Cloud（推奨）

1. https://cloud.laravel.com で GitHub リポジトリを接続。
2. **Environment** を作成（例: `production`）。ビルド設定は Laravel を自動検出:
   - Build: `composer install --no-dev --optimize-autoloader && npm ci && npm run build`
   - Deploy: `php artisan migrate --force`（+ `optimize`）
3. **Environment variables** に本番値を設定（下記「5. 本番 .env」）。
   `DB_*` は **pooled** ホストを使う。
4. **Scheduler** を有効化（`bootstrap/app.php` の `withSchedule` が動く）。
5. **Worker** を1つ追加: `php artisan queue:work --tries=3`（`QUEUE_CONNECTION=database`）。
6. **Domain** を設定し DNS を向ける。SSL は自動。
7. Deploy 実行。

初回のみ、Neon への `migrate` とデータ投入は「1a / 1b」でローカルから済ませておく
（Laravel Cloud の deploy でも `migrate --force` は走るが、pooled 経由なので既に適用済みなら no-op）。

---

## 4. Laravel Forge + VPS（代替）

1. Forge で サーバー作成（Hetzner / DigitalOcean 等、PHP 8.3）。
2. Site 作成 → GitHub リポジトリを接続、ブランチ `main`。
3. **Deploy Script**（`deploy.sh` 相当、Forge の「Deploy Script」欄）:

   ```bash
   cd $FORGE_SITE_PATH
   git pull origin main
   composer install --no-dev --optimize-autoloader --no-interaction
   npm ci && npm run build
   php artisan migrate --force
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan event:cache
   ( flock -w 10 9 || exit 1
     echo 'Restarting FPM...'; sudo -S service php8.3-fpm reload ) 9>/tmp/fpmlock
   ```

4. **Environment**（Forge の「Environment」欄）に本番 .env（下記5）。
5. **Scheduler**: Forge の「Scheduler」で `php artisan schedule:run` を毎分。
6. **Queue Worker**: Forge の「Daemons」で `php artisan queue:work --tries=3`。
7. Site の SSL（Let's Encrypt）を発行、DNS を向ける。

---

## 5. 本番 .env

```env
APP_NAME="CtoS"
APP_ENV=production
APP_KEY=              # php artisan key:generate で生成（Laravel Cloud は自動）
APP_DEBUG=false
APP_URL=https://あなたのドメイン
APP_LOCALE=ja
APP_DEFAULT_SITE=www
APP_SUPER_ADMIN_MEMBER_IDS=            # 全サイト管理できる運営者の member_id（カンマ区切り）

# DB（アプリ実行時は pooled ホスト）
DB_CONNECTION=pgsql
DB_HOST=ep-xxxx-pooler.REGION.aws.neon.tech
DB_PORT=5432
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...
DB_SSLMODE=require
DB_EMULATE_PREPARES=true              # Neon pooled(PgBouncer) では必須。無いと 25P02
# ↑ または DB_URL に pooled 文字列をまるごと

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

# R2（上記 2）
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=auto
AWS_BUCKET=cto-php
AWS_ENDPOINT=https://<accountid>.r2.cloudflarestorage.com
AWS_USE_PATH_STYLE_ENDPOINT=true

# Stripe（本番キー）— BILLING.md
STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...       # 本番は `php artisan cashier:webhook` で登録後に取得
CASHIER_CURRENCY=jpy
CASHIER_CURRENCY_LOCALE=ja
STRIPE_PRICE_STANDARD=price_...       # 本番ダッシュボードで作成
STRIPE_PRICE_PRO=price_...

# メール（Resend / Tokyo region、cto.jp を Verified 済み）
MAIL_MAILER=resend
RESEND_API_KEY=re_...
MAIL_FROM_ADDRESS="noreply@cto.jp"
MAIL_FROM_NAME="CtoS"
```

---

## 6. デプロイ後の確認

- `https://ドメイン/up` が 200
- トップ `/`・`/login`・`/admin/login` が表示される
- 管理員アカウントで `/admin` に入れる
- `/admin/billing` でプランが表示される（Stripe キー設定済みなら契約ボタン）
- ファイルのアップロード / ダウンロードが通る（R2 疎通）
- `php artisan schedule:list` にスケジュールが出る

## 7. 更新デプロイ

Laravel Cloud / Forge どちらも **`main` に push すれば自動デプロイ**（Deploy Script / ビルド設定に沿って
`composer install --no-dev` → `npm run build` → `migrate --force` → 各種 `*:cache`）。

## メモ

- `public/build` は `.gitignore` 対象。**必ずデプロイ時に `npm run build` すること**。
- 論理削除された孤児添付の掃除はスケジューラの `attachments:prune`（週次）で自動。
- テナントを増やすときは `schema-gen/load_tenant.php`（`MULTITENANCY.md`）。
