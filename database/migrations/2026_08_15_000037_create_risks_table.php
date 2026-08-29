<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 旧Access: risk テーブルから自動生成
     * 論理グループ: tenant（テナントごとの業務DB）
     */
    public function up(): void
    {
        Schema::create('risks', function (Blueprint $table) {
            $table->dateTime('acteddate')->nullable();
            $table->string('approver', 255)->nullable();
            $table->string('area', 255)->nullable();
            $table->integer('category')->nullable();
            $table->dateTime('completion_date')->nullable();
            $table->text('content')->nullable();
            $table->integer('delete_to')->nullable();
            $table->dateTime('dotoday')->nullable();
            $table->dateTime('duedate')->nullable();
            $table->id('id');
            $table->integer('impact2cost')->nullable();
            $table->integer('impact2quality')->nullable();
            $table->integer('impact2schedule')->nullable();
            $table->integer('impact2scope')->nullable();
            $table->integer('indicator')->nullable();
            $table->string('maker', 255)->nullable();
            $table->dateTime('monitoreddate')->nullable();
            $table->integer('monitorfrequency')->nullable();
            $table->string('person_do', 255)->nullable();
            $table->integer('probability')->nullable();
            $table->dateTime('renewdate')->nullable();
            $table->text('situation')->nullable();
            $table->integer('status');
            $table->string('strategy', 255)->nullable();
            $table->integer('team_id')->nullable();
            $table->string('title', 255);
            $table->text('trigger')->nullable();
            $table->string('unit', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risks');
    }
};
