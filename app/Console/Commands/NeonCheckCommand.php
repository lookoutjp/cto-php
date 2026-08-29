<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Neon（config/database.php の 'neon' 接続 / NEON_DATABASE_URL）への疎通確認。
 *
 *   php artisan db:neon-check              … 接続・バージョン・テーブル数を表示
 *   php artisan db:neon-check --migrate    … さらに neon 接続へ migrate を実行
 */
class NeonCheckCommand extends Command
{
    protected $signature = 'db:neon-check {--migrate : neon 接続に対して migrate を実行する}';

    protected $description = 'Neon(PostgreSQL) への疎通を確認する';

    public function handle(): int
    {
        if (blank(config('database.connections.neon.url'))) {
            $this->error('NEON_DATABASE_URL が未設定です。.env に Neon の接続文字列を入れてください。');
            $this->line('  NEON_DATABASE_URL=postgresql://USER:PASS@ep-xxxx.REGION.aws.neon.tech/DBNAME?sslmode=require');

            return self::FAILURE;
        }

        try {
            $version = DB::connection('neon')->scalar('select version()');
            $this->info('接続OK');
            $this->line('  '.$version);

            $tables = DB::connection('neon')->scalar(
                "select count(*) from information_schema.tables where table_schema = 'public'"
            );
            $this->line("  public スキーマのテーブル数: {$tables}");

            $migrated = Schema::connection('neon')->hasTable('migrations')
                ? DB::connection('neon')->table('migrations')->count()
                : 0;
            $this->line("  適用済みマイグレーション: {$migrated}");
        } catch (Throwable $e) {
            $this->error('接続に失敗しました: '.$e->getMessage());

            return self::FAILURE;
        }

        if ($this->option('migrate')) {
            $this->newLine();
            $this->warn('neon 接続に対して migrate を実行します。');

            $this->call('migrate', ['--database' => 'neon', '--force' => true]);
        }

        return self::SUCCESS;
    }
}
