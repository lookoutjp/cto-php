<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 旧Access: link テーブルから自動生成
     * 論理グループ: tenant（テナントごとの業務DB）
     */
    public function up(): void
    {
        Schema::create('links', function (Blueprint $table) {
            $table->string('allow', 50)->nullable();
            $table->text('com')->nullable();
            $table->string('email', 255)->nullable();
            $table->string('hits', 255)->nullable();
            $table->string('homepage', 255)->nullable();
            $table->id('id');
            $table->text('jj')->nullable();
            $table->dateTime('linktime')->nullable();
            $table->string('logo', 255)->nullable();
            $table->string('name', 255)->nullable();
            $table->string('site', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('links');
    }
};
