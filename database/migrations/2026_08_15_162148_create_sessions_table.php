<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Laravel標準のセッションストア用テーブル。
     * 旧Accessの websession（web_sessions）テーブルとは別物（そちらはアプリ独自のログイン中サイト管理用）。
     */
    public function up(): void
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            // Laravelのセッションハンドラが内部的に固定で使う列名なので user_id のままにする
            // (中身はmembers.member_idの値=UUID文字列が入る)
            $table->string('user_id', 50)->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
