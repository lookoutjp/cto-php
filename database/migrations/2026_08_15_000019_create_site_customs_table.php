<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 旧Access: custom テーブルから自動生成
     * 論理グループ: tenant（テナントごとの業務DB）
     */
    public function up(): void
    {
        Schema::create('site_customs', function (Blueprint $table) {
            $table->text('custcont')->nullable();
            $table->string('custname', 255)->primary();
            $table->text('f1')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_customs');
    }
};
