<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 旧Access: SurveyChoiceResult テーブルから自動生成
     * 論理グループ: tenant（テナントごとの業務DB）
     */
    public function up(): void
    {
        Schema::create('survey_choice_results', function (Blueprint $table) {
            $table->integer('choice_number')->nullable();
            $table->dateTime('dt')->nullable();
            $table->id('id');
            $table->string('member_id', 255)->nullable();
            $table->integer('survey_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_choice_results');
    }
};
