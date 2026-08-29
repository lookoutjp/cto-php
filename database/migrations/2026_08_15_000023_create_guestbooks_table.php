<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 旧Access: guestbook テーブルから自動生成
     * 論理グループ: tenant（テナントごとの業務DB）
     */
    public function up(): void
    {
        Schema::create('guestbooks', function (Blueprint $table) {
            $table->dateTime('answer_date')->nullable();
            $table->integer('category')->nullable();
            $table->text('content')->nullable();
            $table->dateTime('create_date')->nullable();
            $table->string('email', 50)->nullable();
            $table->string('homepage', 50)->nullable();
            $table->id('id');
            $table->integer('orders')->nullable();
            $table->string('parent', 50)->nullable();
            $table->text('revert')->nullable();
            $table->dateTime('revert_date')->nullable();
            $table->integer('space_num');
            $table->string('title', 255)->nullable();
            $table->string('top', 50)->nullable();
            $table->string('user_name', 50)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guestbooks');
    }
};
