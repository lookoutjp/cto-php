<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * WBS スケジューリング拡張:
     *  - relations に依存タイプ(FS/SS/FF/SF)とラグ日数を追加
     *  - サイトごとの休日カレンダー(holidays)
     */
    public function up(): void
    {
        Schema::table('relations', function (Blueprint $table) {
            $table->string('dep_type', 2)->default('FS');   // FS / SS / FF / SF（rtype='fromto' のときのみ有効）
            $table->integer('lag_days')->default(0);        // 正=ラグ（遅延）, 負=リード（前倒し）
        });

        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->string('site_id', 50);
            $table->date('date');
            $table->string('label', 100)->nullable();
            $table->index(['site_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::table('relations', function (Blueprint $table) {
            $table->dropColumn(['dep_type', 'lag_days']);
        });
        Schema::dropIfExists('holidays');
    }
};
