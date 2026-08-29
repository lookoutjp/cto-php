<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 旧Access: ContentComment テーブルから自動生成
     * 論理グループ: tenant（テナントごとの業務DB）
     */
    public function up(): void
    {
        Schema::create('content_comments', function (Blueprint $table) {
            $table->text('comment')->nullable();
            $table->string('content_id', 50)->nullable();
            $table->id('id');
            $table->string('member_id', 50)->nullable();
            $table->text('name')->nullable();
            $table->integer('ninshou')->nullable();
            $table->string('time', 50)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_comments');
    }
};
