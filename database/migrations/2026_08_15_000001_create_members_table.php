<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 旧Access: member テーブルから自動生成
     * 論理グループ: central（全サイト共有DB）
     */
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->string('address', 50)->nullable();
            $table->string('addressread', 50)->nullable();
            $table->string('answer', 250)->nullable();
            $table->string('appeal', 255)->nullable();
            $table->string('code', 50)->nullable();
            $table->string('dayphone', 50)->nullable();
            $table->string('email', 50)->nullable();
            $table->string('hp', 50)->nullable();
            $table->integer('id')->nullable();
            $table->text('introduce')->nullable();
            $table->dateTime('loginedtime')->nullable();
            $table->integer('login_error_times')->nullable();
            $table->string('magazine', 50)->nullable();
            $table->string('member_id', 50)->primary();
            $table->string('name', 50)->nullable();
            $table->string('nameread', 50)->nullable();
            $table->integer('online')->nullable();
            $table->string('password', 255)->nullable(); // 旧Accessは50文字だったが、bcrypt/argon2ハッシュ格納のため拡張
            $table->string('phone', 50)->nullable();
            $table->integer('pointm')->nullable();
            $table->dateTime('pointmtime')->nullable();
            $table->string('question', 250)->nullable();
            $table->string('regtime', 50)->nullable();
            $table->string('sex', 50)->nullable();
            $table->dateTime('timerenew')->nullable();
            $table->rememberToken(); // Laravelの「ログイン状態を保持する」機能に必要。旧Accessスキーマには存在しなかった追加列。
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
