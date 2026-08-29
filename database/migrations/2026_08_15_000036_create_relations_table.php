<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 旧Access: relation テーブルから自動生成
     * 論理グループ: tenant（テナントごとの業務DB）
     */
    public function up(): void
    {
        Schema::create('relations', function (Blueprint $table) {
            $table->integer('delete_to')->nullable();
            $table->id('id');
            $table->integer('id_from')->nullable();
            $table->string('id_from_kind', 255);
            $table->integer('id_to');
            $table->string('id_to_kind', 255)->nullable();
            $table->string('rtype', 255);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('relations');
    }
};
