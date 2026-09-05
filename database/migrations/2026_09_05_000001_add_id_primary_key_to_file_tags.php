<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * file_tags には単一主キーが無く、Filament の一覧（order by "id"）で
 * 500 エラーになっていた。自動採番の id を追加する。
 * 既存の tag_id 列（files.tag_id 等の参照キー）はそのまま。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('file_tags', function (Blueprint $table) {
            $table->bigIncrements('id')->first();
        });
    }

    public function down(): void
    {
        Schema::table('file_tags', function (Blueprint $table) {
            $table->dropColumn('id');
        });
    }
};
