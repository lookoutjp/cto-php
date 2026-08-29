# GreenGeeksへのデプロイ手順

前提: cPanelにSSH/Terminalアクセスあり、PHP 8.2系、MariaDB 11.4系（`SELECT VERSION();`で確認済み）。

## 1. 本番用データベースの作成

cPanelの「MySQL Databases」で以下を作成する。

- データベース名（例: `youraccount_cto`）
- 専用ユーザーとパスワードを作成し、そのデータベースに全権限を付与

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

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=youraccount_cto
DB_USERNAME=youraccount_ctouser
DB_PASSWORD=（cPanelで設定したパスワード）

SESSION_DRIVER=database
QUEUE_CONNECTION=database
```

## 5. migrationの実行

```bash
php artisan migrate --force
```

（`--force`は本番環境での確認プロンプトをスキップするフラグ）

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
