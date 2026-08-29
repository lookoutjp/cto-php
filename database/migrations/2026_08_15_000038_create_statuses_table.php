<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 旧Access: status テーブルから自動生成
     * 論理グループ: tenant（テナントごとの業務DB）
     */
    public function up(): void
    {
        Schema::create('statuses', function (Blueprint $table) {
            $table->id('id');
            $table->integer('junban')->nullable();
            $table->string('kind', 255)->nullable();
            $table->integer('percent')->nullable();
            $table->text('statuscomment')->nullable();
            $table->string('statusname', 255);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('statuses');
    }
};
