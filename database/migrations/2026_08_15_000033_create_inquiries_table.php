<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 旧Access: otoi テーブルから自動生成
     * 論理グループ: tenant（テナントごとの業務DB）
     */
    public function up(): void
    {
        Schema::create('inquiries', function (Blueprint $table) {
            $table->string('address', 250)->nullable();
            $table->string('code', 50)->nullable();
            $table->dateTime('create_date')->nullable();
            $table->string('customer_name', 50)->nullable();
            $table->string('customer_nameread', 50)->nullable();
            $table->string('dayphone', 50)->nullable();
            $table->string('email', 50)->nullable();
            $table->id('id');
            $table->string('member_id', 50)->nullable();
            $table->string('phone', 50)->nullable();
            $table->text('remark')->nullable();
            $table->integer('state')->nullable();
            $table->string('title', 50)->nullable();
            $table->dateTime('treated_date')->nullable();
            $table->text('treated_remark')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiries');
    }
};
