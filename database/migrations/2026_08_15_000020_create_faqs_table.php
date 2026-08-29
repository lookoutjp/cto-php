<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 旧Access: faq テーブルから自動生成
     * 論理グループ: tenant（テナントごとの業務DB）
     */
    public function up(): void
    {
        Schema::create('faqs', function (Blueprint $table) {
            $table->text('answer')->nullable();
            $table->integer('clicks')->nullable();
            $table->id('id');
            $table->text('question')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};
