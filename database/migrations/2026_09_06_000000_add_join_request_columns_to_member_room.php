<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 会員の「サイト加入申請 → 管理員承認」フロー用。
 *
 *   applied_at  … 加入申請日時（既存会員が他サイトへ加入申請したときにセット）
 *   approved_at … 承認日時（管理員が「会員権限」画面で承認したときにセット）
 *
 * 承認待ち  = applied_at IS NOT NULL AND approved_at IS NULL（ninshou は NULL）
 * 承認済み  = approved_at IS NOT NULL、または両方 NULL（＝旧データ・通常登録）
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('member_room', function (Blueprint $table) {
            $table->timestamp('applied_at')->nullable()->after('ninshou');
            $table->timestamp('approved_at')->nullable()->after('applied_at');
        });

        // 承認待ちの行は ninshou = NULL（権限未付与）で持つため NOT NULL を外す。
        Schema::table('member_room', function (Blueprint $table) {
            $table->integer('ninshou')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('member_room', function (Blueprint $table) {
            $table->dropColumn(['applied_at', 'approved_at']);
        });
    }
};
