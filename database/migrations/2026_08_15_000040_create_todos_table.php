<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 旧Access: todo テーブルから自動生成
     * 論理グループ: tenant（テナントごとの業務DB）
     */
    public function up(): void
    {
        Schema::create('todos', function (Blueprint $table) {
            $table->string('approver', 255)->nullable();
            $table->integer('category')->nullable();
            $table->text('completioncriteria')->nullable();
            $table->dateTime('completion_date')->nullable();
            $table->text('content')->nullable();
            $table->integer('delete_to')->nullable();
            $table->dateTime('dotoday')->nullable();
            $table->dateTime('duedate')->nullable();
            $table->id('id');
            $table->string('maker', 255)->nullable();
            $table->string('person_do', 255)->nullable();
            $table->dateTime('renewdate')->nullable();
            $table->text('situation')->nullable();
            $table->integer('status')->nullable();
            $table->integer('team_id')->nullable();
            $table->string('title', 255);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('todos');
    }
};
