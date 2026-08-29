<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 旧Access: topmenu テーブルから自動生成
     * 論理グループ: tenant（テナントごとの業務DB）
     */
    public function up(): void
    {
        Schema::create('top_menus', function (Blueprint $table) {
            $table->id('id');
            $table->double('junban')->nullable();
            $table->string('linkaddress', 255)->nullable();
            $table->string('menuname', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('top_menus');
    }
};
