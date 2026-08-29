<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 旧Access: maillist テーブルから自動生成
     * 論理グループ: tenant（テナントごとの業務DB）
     */
    public function up(): void
    {
        Schema::create('mail_lists', function (Blueprint $table) {
            $table->string('email', 50)->nullable();
            $table->id('id');
            $table->string('mail_list_sort', 50)->nullable();
            $table->string('name', 50)->nullable();
            $table->string('remark', 50)->nullable();
            $table->string('time', 50)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_lists');
    }
};
