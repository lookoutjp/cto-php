<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 旧Access: control テーブルから自動生成
     * 論理グループ: tenant（テナントごとの業務DB）
     */
    public function up(): void
    {
        Schema::create('controls', function (Blueprint $table) {
            $table->dateTime('adddate')->nullable();
            $table->string('atonce', 50)->nullable();
            $table->text('content')->nullable();
            $table->string('difficult', 50)->nullable();
            $table->text('editdate')->nullable();
            $table->string('editor', 50)->nullable();
            $table->id('id');
            $table->string('importance', 50)->nullable();
            $table->string('irai', 255)->nullable();
            $table->string('joutai', 50)->nullable();
            $table->string('tantou', 50)->nullable();
            $table->text('title')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('controls');
    }
};
