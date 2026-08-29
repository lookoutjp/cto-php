<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 旧Access: sysversion テーブルから自動生成
     * 論理グループ: central（全サイト共有DB）
     */
    public function up(): void
    {
        Schema::create('sysversions', function (Blueprint $table) {
            $table->id('id');
            $table->string('kubun', 50)->nullable();
            $table->text('versioninfo')->nullable();
            $table->string('versionlabel', 50);
            $table->dateTime('versionupday');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sysversions');
    }
};
