<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 旧Access: lebel テーブルから自動生成
     * 論理グループ: central（全サイト共有DB）
     */
    public function up(): void
    {
        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->integer('fatherlevel')->nullable();
            $table->integer('level');
            $table->string('levelname', 50)->nullable();
            $table->string('site_id', 50);
            $table->unique(['level', 'site_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('levels');
    }
};
