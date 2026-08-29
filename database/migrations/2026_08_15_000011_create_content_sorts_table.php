<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 旧Access: ContentSort テーブルから自動生成
     * 論理グループ: tenant（テナントごとの業務DB）
     */
    public function up(): void
    {
        Schema::create('content_sorts', function (Blueprint $table) {
            $table->string('categoryimage', 250)->nullable();
            $table->integer('father_id')->nullable();
            $table->id('id');
            $table->text('introduce')->nullable();
            $table->integer('junban')->nullable();
            $table->integer('koukaiflag')->nullable();
            $table->text('link')->nullable();
            $table->string('manager', 50)->nullable();
            $table->string('name', 50)->nullable();
            $table->integer('ninshou')->nullable();
            $table->text('ninshouspecial')->nullable();
            $table->string('tobbs', 50)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_sorts');
    }
};
