<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 旧Access: news テーブルから自動生成
     * 論理グループ: tenant（テナントごとの業務DB）
     */
    public function up(): void
    {
        Schema::create('news', function (Blueprint $table) {
            $table->dateTime('adddatetime')->nullable();
            $table->integer('clicks')->nullable();
            $table->text('content')->nullable();
            $table->dateTime('editdatetime')->nullable();
            $table->id('id');
            $table->string('istop', 50)->nullable();
            $table->dateTime('newsdate')->nullable();
            $table->string('news_img', 50)->nullable();
            $table->text('title')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
