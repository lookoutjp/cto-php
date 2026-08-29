<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 旧Access: SurveyChoice テーブルから自動生成
     * 論理グループ: tenant（テナントごとの業務DB）
     */
    public function up(): void
    {
        Schema::create('survey_choices', function (Blueprint $table) {
            $table->text('choice_explain')->nullable();
            $table->integer('choice_number')->nullable();
            $table->string('choice_title', 255)->nullable();
            $table->id('id');
            $table->integer('survey_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_choices');
    }
};
