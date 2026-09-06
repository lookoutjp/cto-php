<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * members.signup_site … 会員が最初に登録した（＝申請した）サイトの site_id。
 * /register 経由の会員登録時にセットする。旧データ・テナント作成者は NULL のまま。
 * cto.jp（既定サイト）の会員一覧で「どのサイトからの登録か」を表示するのに使う。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('signup_site', 50)->nullable()->after('member_id');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('signup_site');
        });
    }
};
