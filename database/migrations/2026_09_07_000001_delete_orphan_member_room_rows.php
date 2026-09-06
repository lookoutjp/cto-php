<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * members に存在しない member_id の member_room 行（＝会員削除後に残った孤児）を掃除する。
 * 以降は Member モデルの deleting フックが member_room を連動削除する。
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('member_room')
            ->whereNotIn('member_id', fn ($q) => $q->select('member_id')->from('members'))
            ->delete();
    }

    public function down(): void
    {
        // 削除したデータは復元不可。
    }
};
