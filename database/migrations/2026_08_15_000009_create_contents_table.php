<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 旧Access: Content テーブルから自動生成
     * 論理グループ: tenant（テナントごとの業務DB）
     */
    public function up(): void
    {
        Schema::create('contents', function (Blueprint $table) {
            $table->dateTime('adddatetime')->nullable();
            $table->integer('addtime')->nullable();
            $table->integer('clicks')->nullable();
            $table->integer('commentok')->nullable();
            $table->integer('content_sort')->nullable();
            $table->dateTime('createdt')->nullable();
            $table->string('delitiji', 50)->nullable();
            $table->dateTime('edittime')->nullable();
            $table->text('explain')->nullable();
            $table->text('hlsyosailink')->nullable();
            $table->id('id');
            $table->text('introduce')->nullable();
            $table->integer('junban')->nullable();
            $table->text('keyword')->nullable();
            $table->string('member_id', 225)->nullable();
            $table->text('name')->nullable();
            $table->text('nameintro')->nullable();
            $table->integer('ninshou')->nullable();
            $table->integer('ok')->nullable();
            $table->integer('okngflag')->nullable();
            $table->string('oktime', 50)->nullable();
            $table->string('owner', 50)->nullable();
            $table->integer('recommend')->nullable();
            $table->dateTime('recommend_date')->nullable();
            $table->integer('survey_id')->nullable();
            $table->string('syokai', 50)->nullable();
            $table->string('syosai', 50)->nullable();
            $table->text('title2')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};
