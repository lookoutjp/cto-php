<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 旧Access: wbs テーブルから自動生成
     * 論理グループ: tenant（テナントごとの業務DB）
     */
    public function up(): void
    {
        Schema::create('wbs', function (Blueprint $table) {
            $table->integer('actualdays')->nullable();
            $table->string('approver', 255)->nullable();
            $table->dateTime('complete_date')->nullable();
            $table->text('content')->nullable();
            $table->integer('deep')->nullable();
            $table->integer('delete_to')->nullable();
            $table->dateTime('dotoday')->nullable();
            $table->dateTime('duedate')->nullable();
            $table->integer('father_id')->nullable();
            $table->dateTime('godate')->nullable();
            $table->id('id');
            $table->integer('iscategory')->nullable();
            $table->integer('jun')->nullable();
            $table->integer('junban')->nullable();
            $table->string('maker', 255)->nullable();
            $table->string('person_do', 255)->nullable();
            $table->dateTime('renewdate')->nullable();
            $table->text('situation')->nullable();
            $table->dateTime('start_date')->nullable();
            $table->integer('status');
            $table->integer('team_id')->nullable();
            $table->string('title', 255);
            $table->integer('tododays')->nullable();
            $table->integer('tododays_ed')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wbs');
    }
};
