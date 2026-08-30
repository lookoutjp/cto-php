<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * コンテンツ / WBS / タスクへの添付ファイル（旧ASPには無かった新機能）。
     * 実体は S3/R2、`files` テーブル（独立ライブラリ）とは別。
     */
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->string('site_id', 50);
            $table->string('attachable_type');
            $table->unsignedBigInteger('attachable_id');
            $table->string('storage_key', 512);
            $table->string('original_name');
            $table->string('ext', 20)->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('mime')->nullable();
            $table->string('member_id')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('site_id');
            $table->index(['attachable_type', 'attachable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
