<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 旧Access: category テーブルから自動生成
     * 論理グループ: tenant（テナントごとの業務DB）
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->string('categoryname', 255);
            $table->id('id');
            $table->integer('junban')->nullable();
            $table->string('kind', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
