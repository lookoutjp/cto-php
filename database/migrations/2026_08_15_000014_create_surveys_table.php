<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 旧Access: Survey テーブルから自動生成
     * 論理グループ: tenant（テナントごとの業務DB）
     */
    public function up(): void
    {
        Schema::create('surveys', function (Blueprint $table) {
            $table->dateTime('answer_due_date')->nullable();
            $table->integer('delete_to')->nullable();
            $table->id('id');
            $table->string('member_id', 255)->nullable();
            $table->boolean('open_yn');
            $table->integer('selectable_numbers')->nullable();
            $table->boolean('specify_yn');
            $table->string('title', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surveys');
    }
};
