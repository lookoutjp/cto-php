<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 業務(tenant)テーブルに site_id を追加し、単一DB + 行レベルのテナント分離へ移行する。
     *
     * 旧設計は templatedb.mdb をテナントごとに物理コピーしていたため、これらの
     * テーブルには site_id が無かった。既存行はすべて主力サイト 'www' のデータ。
     *
     * ここでは nullable のまま入れる（NOT NULL 化は全テナント投入後の別マイグレーション）。
     * アプリ側は App\Models\Concerns\BelongsToSite が読み書き両方をスコープする。
     */
    private array $tables = [
        'categories', 'change_requests', 'contents', 'content_comments', 'content_sorts',
        'controls', 'site_customs', 'faqs', 'files', 'file_tags', 'guestbooks',
        'guestbook_categories', 'homework_sorts', 'links', 'logs', 'log_okngs', 'mail_lists',
        'messages', 'complaints', 'news', 'inquiries', 'problems', 'products', 'relations',
        'risks', 'routine_works', 'routine_work_lists', 'statuses', 'surveys', 'survey_choices',
        'survey_choice_results', 'survey_reply_lists', 'todos', 'top_menus', 'wbs',
    ];

    public function up(): void
    {
        foreach ($this->tables as $t) {
            if (! Schema::hasTable($t) || Schema::hasColumn($t, 'site_id')) {
                continue;
            }

            Schema::table($t, function (Blueprint $table) {
                $table->string('site_id', 50)->nullable();
                $table->index('site_id');
            });

            DB::table($t)->whereNull('site_id')->update(['site_id' => 'www']);
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $t) {
            if (Schema::hasTable($t) && Schema::hasColumn($t, 'site_id')) {
                Schema::table($t, function (Blueprint $table) {
                    $table->dropColumn('site_id');
                });
            }
        }
    }
};
