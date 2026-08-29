<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 旧Access: message テーブルから自動生成
     * 論理グループ: tenant（テナントごとの業務DB）
     */
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->text('content')->nullable();
            $table->integer('delete_from');
            $table->integer('delete_to')->nullable();
            $table->string('from', 225)->nullable();
            $table->id('id');
            $table->integer('readed')->nullable();
            $table->dateTime('time')->nullable();
            $table->string('to', 225)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
