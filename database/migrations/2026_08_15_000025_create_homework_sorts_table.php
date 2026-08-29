<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 旧Access: homeworksort テーブルから自動生成
     * 論理グループ: tenant（テナントごとの業務DB）
     */
    public function up(): void
    {
        Schema::create('homework_sorts', function (Blueprint $table) {
            $table->integer('father_id')->nullable();
            $table->id('id');
            $table->text('introduce')->nullable();
            $table->integer('junban')->nullable();
            $table->text('link')->nullable();
            $table->string('name', 50)->nullable();
            $table->string('ninshou', 50)->nullable();
            $table->string('tobbs', 50)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homework_sorts');
    }
};
