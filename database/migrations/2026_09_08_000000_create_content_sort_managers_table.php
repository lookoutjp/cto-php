<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * カテゴリ管理員（content_sorts 単位）。会員が「カテゴリ管理員になる」申請をし、
 * スーパー管理員が承認する。スーパー管理員が直接指定した場合も status='approved' で入る。
 *
 * approved な行 = その会員はそのカテゴリ（配下すべて）の管理員:
 *   サブカテゴリ追加 / 投稿追加（直接公開）/ 会員投稿の承認・却下 ができる。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_sort_managers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('content_sort_id');
            $table->string('member_id', 50);
            $table->string('site_id', 50);
            $table->string('status', 20)->default('pending'); // pending | approved
            $table->timestamp('applied_at')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->string('decided_by', 50)->nullable(); // 承認 or 直接指定したスーパー管理員の member_id

            $table->unique(['content_sort_id', 'member_id']);
            $table->index('site_id');
            $table->index(['member_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_sort_managers');
    }
};
