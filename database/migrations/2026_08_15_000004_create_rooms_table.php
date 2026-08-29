<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 旧Access: room テーブルから自動生成
     * 論理グループ: central（全サイト共有DB）
     */
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->string('comaddress', 50)->nullable();
            $table->string('comemail', 50)->nullable();
            $table->string('comfax', 50)->nullable();
            $table->string('comname', 50)->nullable();
            $table->string('comomanager', 50)->nullable();
            $table->string('comphone', 50)->nullable();
            $table->string('compostcode', 50)->nullable();
            $table->text('copyright')->nullable();
            $table->string('favicon', 255)->nullable();
            $table->text('function_list')->nullable();
            $table->string('homepagemainimage', 250)->nullable();
            $table->integer('id')->nullable();
            $table->string('komon', 50)->nullable();
            $table->string('logo', 255)->nullable();
            $table->integer('logoheight')->nullable();
            $table->integer('logowidth')->nullable();
            $table->string('manager_shouko', 50)->nullable();
            $table->text('managerwords')->nullable();
            $table->string('online', 50)->nullable();
            $table->string('pagebackimage', 250)->nullable();
            $table->string('pagebackimagerepeat', 250)->nullable();
            $table->string('pagetopimage', 250)->nullable();
            $table->string('pagewidth', 50)->nullable();
            $table->string('sitebgcolor', 255)->nullable();
            $table->string('sitecolor', 50)->nullable();
            $table->string('sitedomain', 50)->nullable();
            $table->string('site_id', 50)->primary();
            $table->text('siteintro')->nullable();
            $table->integer('site_joutai');
            $table->string('site_mail', 50)->nullable();
            $table->string('sitename', 250)->nullable();
            $table->string('sitename_color', 50)->nullable();
            $table->string('smtpid', 50)->nullable();
            $table->string('smtppass', 50)->nullable();
            $table->string('smtpserver', 50)->nullable();
            $table->integer('sw_koukoku')->nullable();
            $table->string('webmanager', 50)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
