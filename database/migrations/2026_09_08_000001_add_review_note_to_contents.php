<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * contents.review_note … 会員投稿をカテゴリ管理員が「却下（下書きに差し戻し）」したときの理由。
 * ok=0 かつ review_note があるとき、投稿者の「投稿の管理」画面に表示する。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->text('review_note')->nullable()->after('ok');
        });
    }

    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropColumn('review_note');
        });
    }
};
