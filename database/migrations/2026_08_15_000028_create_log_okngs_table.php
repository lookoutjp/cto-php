<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 旧Access: log_OKNG テーブルから自動生成
     * 論理グループ: tenant（テナントごとの業務DB）
     */
    public function up(): void
    {
        Schema::create('log_okngs', function (Blueprint $table) {
            $table->string('dt', 255)->nullable();
            $table->id('id');
            $table->string('kind', 255)->nullable();
            $table->integer('okng')->nullable();
            $table->integer('the_id')->nullable();
            $table->string('userid', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_okngs');
    }
};
