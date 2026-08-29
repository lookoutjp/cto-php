<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 旧Access: log テーブルから自動生成
     * 論理グループ: tenant（テナントごとの業務DB）
     */
    public function up(): void
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->text('after')->nullable();
            $table->text('before')->nullable();
            $table->dateTime('dt')->nullable();
            $table->string('fieldname', 255);
            $table->id('id');
            $table->string('ids', 255)->nullable();
            $table->string('kind', 255);
            $table->integer('sakujoflag');
            $table->string('username', 255);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
