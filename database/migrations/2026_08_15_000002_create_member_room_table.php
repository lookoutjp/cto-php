<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 旧Access: memberroom テーブルから自動生成
     * 論理グループ: central（全サイト共有DB）
     */
    public function up(): void
    {
        Schema::create('member_room', function (Blueprint $table) {
            $table->id();
            $table->integer('legacy_id')->nullable();
            $table->string('member_id', 50);
            $table->integer('ninshou');
            $table->string('site_id', 50);
            $table->unique(['member_id', 'site_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_room');
    }
};
