<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 旧Access: RoutineWorkList テーブルから自動生成
     * 論理グループ: tenant（テナントごとの業務DB）
     */
    public function up(): void
    {
        Schema::create('routine_work_lists', function (Blueprint $table) {
            $table->dateTime('acteddate')->nullable();
            $table->dateTime('actiondate')->nullable();
            $table->dateTime('add_date_time')->nullable();
            $table->string('approver', 255)->nullable();
            $table->integer('category')->nullable();
            $table->string('circle', 255)->nullable();
            $table->string('circle_number', 255)->nullable();
            $table->text('completioncriteria')->nullable();
            $table->dateTime('completion_date')->nullable();
            $table->text('content')->nullable();
            $table->integer('delete_to')->nullable();
            $table->dateTime('dotoday')->nullable();
            $table->integer('hours_et')->nullable();
            $table->integer('hours_et_actual')->nullable();
            $table->id('id');
            $table->integer('junban')->nullable();
            $table->string('maker', 255)->nullable();
            $table->string('person_do', 255)->nullable();
            $table->dateTime('renewdate')->nullable();
            $table->integer('routine_work_id')->nullable();
            $table->text('situation')->nullable();
            $table->integer('status')->nullable();
            $table->integer('team_id')->nullable();
            $table->string('title', 255);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routine_work_lists');
    }
};
