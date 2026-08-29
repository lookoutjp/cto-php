<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * マルチテナントの整合性を DB レベルでも担保する。
 * site_id は BelongsToSite トレイトが必ずセットするが、生 SQL や取り込み処理で
 * 抜けても気付けるよう NOT NULL 制約を張る（保険として既定値 'www' も付与）。
 *
 * 既に NOT NULL の rooms / member_room / levels / holidays は対象外。
 */
return new class extends Migration
{
    /** @var list<string> */
    private array $tables = [
        'categories', 'change_requests', 'complaints', 'content_comments', 'content_sorts',
        'contents', 'controls', 'faqs', 'file_tags', 'files', 'guestbook_categories', 'guestbooks',
        'homework_sorts', 'inquiries', 'links', 'log_okngs', 'logs', 'mail_lists', 'messages',
        'news', 'password_reset_tokens', 'problems', 'products', 'relations', 'risks',
        'routine_work_lists', 'routine_works', 'site_customs', 'statuses', 'survey_choice_results',
        'survey_choices', 'survey_reply_lists', 'surveys', 'todos', 'top_menus', 'wbs', 'web_sessions',
    ];

    private string $default;

    public function __construct()
    {
        $this->default = (string) config('app.default_site', 'www');
    }

    public function up(): void
    {
        foreach ($this->tables as $t) {
            DB::table($t)->whereNull('site_id')->update(['site_id' => $this->default]);
            DB::statement("ALTER TABLE {$t} ALTER COLUMN site_id SET DEFAULT '{$this->default}'");
            DB::statement("ALTER TABLE {$t} ALTER COLUMN site_id SET NOT NULL");
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $t) {
            DB::statement("ALTER TABLE {$t} ALTER COLUMN site_id DROP NOT NULL");
            DB::statement("ALTER TABLE {$t} ALTER COLUMN site_id DROP DEFAULT");
        }
    }
};
