<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 旧Access: monku テーブルから自動生成
     * 論理グループ: tenant（テナントごとの業務DB）
     */
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->integer('father_id')->nullable();
            $table->id('id');
            $table->text('name')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
