<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 旧Access: websession テーブルから自動生成
     * 論理グループ: central（全サイト共有DB）
     */
    public function up(): void
    {
        Schema::create('web_sessions', function (Blueprint $table) {
            $table->dateTime('createdt')->nullable();
            $table->dateTime('expires')->nullable();
            $table->string('lastip', 50)->nullable();
            $table->string('member_id', 50)->nullable();
            $table->string('site_id', 50)->nullable();
            $table->string('token', 100)->primary();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('web_sessions');
    }
};
