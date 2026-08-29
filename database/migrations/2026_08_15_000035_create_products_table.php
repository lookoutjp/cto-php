<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 旧Access: product テーブルから自動生成
     * 論理グループ: tenant（テナントごとの業務DB）
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->integer('category')->nullable();
            $table->text('content')->nullable();
            $table->integer('delete_to')->nullable();
            $table->id('id');
            $table->string('maker', 255)->nullable();
            $table->string('person_do', 255)->nullable();
            $table->dateTime('renewdate')->nullable();
            $table->string('responsible_party', 255)->nullable();
            $table->integer('stage')->nullable();
            $table->integer('status')->nullable();
            $table->string('title', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
