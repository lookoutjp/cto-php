<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 旧Access: filetag テーブルから自動生成
     * 論理グループ: tenant（テナントごとの業務DB）
     */
    public function up(): void
    {
        Schema::create('file_tags', function (Blueprint $table) {
            $table->dateTime('adddt')->nullable();
            $table->string('member_id', 255)->nullable();
            $table->integer('tag_id');
            $table->integer('tag_id_father')->nullable();
            $table->string('tagname', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_tags');
    }
};
