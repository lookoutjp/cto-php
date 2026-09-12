<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 「苦情分類」（旧Access: monku）は他のどの機能からも参照されておらず未使用のため削除する。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('complaints');
    }

    public function down(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->integer('father_id')->nullable();
            $table->id('id');
            $table->text('name')->nullable();
        });
    }
};
