<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * コンテンツコメントの権限(ninshou)は編集画面から外し、DB既定値を 0 にする。
 * 既存行は変更しない（もともと全て 0）。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_comments', function (Blueprint $table) {
            $table->integer('ninshou')->nullable()->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('content_comments', function (Blueprint $table) {
            $table->integer('ninshou')->nullable()->default(null)->change();
        });
    }
};
