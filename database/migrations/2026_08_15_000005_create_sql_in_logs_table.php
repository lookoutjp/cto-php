<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 旧Access: sqlInLog テーブルから自動生成
     * 論理グループ: central（全サイト共有DB）
     */
    public function up(): void
    {
        Schema::create('sql_in_logs', function (Blueprint $table) {
            $table->text('sql_in_cs')->nullable();
            $table->string('sql_in_fs', 255)->nullable();
            $table->id('sql_in_id');
            $table->string('sql_in_ip', 255)->nullable();
            $table->string('sql_in_lang', 255)->nullable();
            $table->string('sql_in_site', 255)->nullable();
            $table->text('sql_in_sj')->nullable();
            $table->dateTime('sql_in_time')->nullable();
            $table->string('sql_in_username', 255)->nullable();
            $table->string('sql_in_web', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sql_in_logs');
    }
};
