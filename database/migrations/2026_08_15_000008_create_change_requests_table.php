<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 旧Access: Change テーブルから自動生成
     * 論理グループ: tenant（テナントごとの業務DB）
     */
    public function up(): void
    {
        Schema::create('change_requests', function (Blueprint $table) {
            $table->dateTime('approve_day')->nullable();
            $table->string('approver', 255)->nullable();
            $table->integer('category')->nullable();
            $table->string('changemaker', 255)->nullable();
            $table->text('content')->nullable();
            $table->integer('delete_to')->nullable();
            $table->text('do_content')->nullable();
            $table->integer('do_hours')->nullable();
            $table->dateTime('done_day')->nullable();
            $table->dateTime('dotoday')->nullable();
            $table->dateTime('duedate')->nullable();
            $table->text('function_name')->nullable();
            $table->integer('hour_estimation')->nullable();
            $table->id('id');
            $table->dateTime('judge_day')->nullable();
            $table->string('judge_person_custmer', 255)->nullable();
            $table->string('judge_person_system', 255)->nullable();
            $table->string('judge_result', 255)->nullable();
            $table->string('maker', 255)->nullable();
            $table->text('ng_reason')->nullable();
            $table->dateTime('occurrence_day')->nullable();
            $table->string('person_do', 255)->nullable();
            $table->dateTime('renewdate')->nullable();
            $table->dateTime('research_reply_day')->nullable();
            $table->string('researcher', 255)->nullable();
            $table->text('research_result')->nullable();
            $table->text('scope_of_impact')->nullable();
            $table->integer('stage')->nullable();
            $table->integer('status')->nullable();
            $table->integer('team_id')->nullable();
            $table->string('title', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('change_requests');
    }
};
