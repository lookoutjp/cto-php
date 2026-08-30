# デプロイ手順

前提: PHP 8.2以上、PostgreSQL 14以上。DBは **Neon**（サーバーレスPostgres）を想定。
アプリのホスティングは Laravel Cloud / Laravel Forge + VPS / Fly.io など。
（旧: GreenGeeks共有ホスティング + MariaDB。有償SaaS化に伴い PostgreSQL へ移行）

## 1. 本番用データベースの作成（Neon）

1. https://neon.tech でプロジェクトを作成（リージョンは利用者に近い場所。無料枠でOK）
2. ダッシュボードの「Connection string」から2つ控える:
   - **Pooled**（`-pooler` 付きホスト）… アプリ実行時に使う
   - **Direct**（`-pooler` なし）… `migrate` 実行時に使う（PgBouncer 経由だと稀に失敗するため）
   形式: `postgresql://USER:PASSWORD@ep-xxxx[-pooler].REGION.aws.neon.tech/DBNAME?sslmode=require`

### 1a. ローカルから Neon への疎通確認（任意）

`.env` に direct のURLを入れて確認できる:

```env
NEON_DATABASE_URL=postgresql://USER:PASS@ep-xxxx.REGION.aws.neon.tech/DBNAME?sslmode=require
```

```bash
php artisan db:neon-check              # 接続・バージョン確認
php artisan db:neon-check --migrate    # neon 接続へ migrate
php schema-gen/load_data.php <CSVルート> neon   # 旧データを Neon へ投入
```

ローカル開発は PostgreSQL 16 をローカルにインストールして使用（`cto_php` データベース）。
本番との差分は `.env` のみ。

## 2. コードの転送

Gitリポジトリを使う場合（推奨）:

```bash
ssh your-account@your-server -p 2222   # GreenGeeksのSSHポート番号はcPanelの「SSH Access」に表示されている
cd ~
git clone <あなたのリポジトリURL> cto-php
cd cto-php
```

Gitを使わない場合は、ローカルの `cto-php` フォルダ一式（`vendor/`と`node_modules/`は除く）をFTP/SFTPでアップロードする。

## 3. 依存パッケージのインストール（本番用）

```bash
composer install --no-dev --optimize-autoloader
```

`node_modules`はアップロード不要。ローカルで`npm run build`した`public/build`フォルダだけをアップロードすればよい。

## 4. `.env`の設定

```bash
cp .env.example .env
php artisan key:generate
```

`.env`を編集し、以下を本番用の値にする。

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://あなたのドメイン
APP_DEFAULT_SITE=www

DB_CONNECTION=pgsql
DB_HOST=ep-xxxx-pooler.REGION.aws.neon.tech   # アプリ実行時は pooled ホスト
DB_PORT=5432
DB_DATABASE=（Neonのデータベース名）
DB_USERNAME=（Neonのユーザー名）
DB_PASSWORD=（Neonのパスワード）
DB_SSLMODE=require
DB_EMULATE_PREPARES=true   # Neon pooled(PgBouncer)経由では必須。無いと 25P02 で落ちる

SESSION_DRIVER=database
QUEUE_CONNECTION=database

# ファイルライブラリの実体（Cloudflare R2 / S3）。詳細は STORAGE.md
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=（R2/S3 のアクセスキー）
AWS_SECRET_ACCESS_KEY=（R2/S3 のシークレット）
AWS_DEFAULT_REGION=auto
AWS_BUCKET=cto-php
AWS_ENDPOINT=https://<accountid>.r2.cloudflarestorage.com
AWS_USE_PATH_STYLE_ENDPOINT=true
```

`composer install` は `league/flysystem-aws-s3-v3` を含む（S3ドライバに必須）。

または `DB_URL` に pooled 接続文字列をまるごと入れてもよい:
```env
DB_URL=postgresql://USER:PASS@ep-xxxx-pooler.REGION.aws.neon.tech/DBNAME?sslmode=require
DB_EMULATE_PREPARES=true
```

> **libpq のバージョン**: Neon は SNI でエンドポイントを判定するため、古い libpq
> （XAMPP同梱の11系など）だと `Endpoint ID is not specified` エラーになる。
> PostgreSQL 14以降の libpq が必要（ローカル開発機では PostgreSQL 16 の
> `libpq.dll` とその依存DLLを PHP フォルダにコピーして対応した）。

## 5. migrationの実行

```bash
php artisan migrate --force
```

Neon の場合、`migrate` は **direct ホスト**（`-pooler` なし）に対して実行する
（一時的に `DB_HOST` を direct に切り替える／`NEON_DATABASE_URL` に direct を入れて
`php artisan db:neon-check --migrate`）。migrate 完了後は pooled ホストに戻す。

（`--force`は本番環境での確認プロンプトをスキップするフラグ）

## 5b. 既存データの投入（初回のみ）

旧Access(.mdb)からのデータ移行は Windows 上で行う（ACE OLEDB が必要なため）:

```powershell
# 1. Access → CSV（Windows PowerShell）
powershell -ExecutionPolicy Bypass -File schema-gen\export_access.ps1 -OutDir <出力先>
# 2. CSV → PostgreSQL
php schema-gen\load_data.php <出力先>
```

`load_data.php` は業務テーブルに `site_id='www'` を自動付与し、bigserialのシーケンスを
投入済み最大IDに合わせる。テナントを増やすときはこのスクリプトを拡張する。

ファイルの実体（旧サーバの `files/{siteid}/WebUp/`）は S3/R2 設定後に:

```powershell
php schema-gen\migrate_legacy_files.php www --dry   # プレビュー
php schema-gen\migrate_legacy_files.php www          # R2 へアップロード
```

## 6. ドキュメントルートを`public/`に向ける

cPanelの「Domains」でこのドメイン（サブドメイン）のドキュメントルートを

```
cto-php/public
```

に変更する。変更できない場合は、`public/`の中身を`public_html`直下にコピーし、`public_html/index.php`内の2箇所のパス（`__DIR__.'/../vendor/autoload.php'`と`__DIR__.'/../bootstrap/app.php'`）を実際の設置場所に合わせて書き換える。

## 7. Cronジョブの設定（スケジューラ）

cPanelの「Cron Jobs」で以下を1分おきに登録する。

```
* * * * * cd /home/youraccount/cto-php && php artisan schedule:run >> /dev/null 2>&1
```

これでLaravelの`queue:work`常駐プロセスの代わりに、`QUEUE_CONNECTION=database`のジョブがバッチ的に処理される（`app/Console/Kernel.php`にスケジュール定義が必要になったら別途追加する）。

## 8. 最終確認

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

本番URLにアクセスし、トップページ・`/login`・`/admin`（Filament管理画面）が表示されることを確認する。

## デプロイのたびに繰り返す作業（更新時）

```bash
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
