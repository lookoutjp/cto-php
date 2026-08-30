<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * files の実体を S3/R2 に置くための列。
     *
     *  - storage_key: オブジェクトストレージ上のキー。null = 旧Access由来で実体未アップロード。
     *  - size_bytes:  ファイルサイズ（プラン容量の従量計算にも使う）。
     *  - mime:        Content-Type。
     */
    public function up(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->string('storage_key', 512)->nullable();
            $table->bigInteger('size_bytes')->nullable();
            $table->string('mime', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->dropColumn(['storage_key', 'size_bytes', 'mime']);
        });
    }
};
