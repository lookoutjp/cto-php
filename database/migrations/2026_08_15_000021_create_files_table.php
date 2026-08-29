<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 旧Access: files テーブルから自動生成
     * 論理グループ: tenant（テナントごとの業務DB）
     */
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->dateTime('adddt')->nullable();
            $table->string('fileext', 255)->nullable();
            $table->text('filename')->nullable();
            $table->string('foldername', 255)->nullable(); // www.mdbのみに存在（templatedb.mdbには無かった列）
            $table->id('id');
            $table->text('intro')->nullable();
            $table->string('member_id', 255)->nullable();
            $table->integer('renban')->nullable();
            $table->text('tag_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
