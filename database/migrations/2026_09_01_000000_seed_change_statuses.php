<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * 変更管理（change_requests）用のステータスを各サイトに投入する。
 * 旧Access には kind='change' の statuses が存在しなかった（画面が保留だった）ため、
 * 変更管理ワークフロー（起票→調査→判定→承認→対応→完了/却下）に沿った標準セットを定義する。
 */
return new class extends Migration
{
    private array $rows = [
        ['junban' => 1, 'statusname' => '起票', 'percent' => 0, 'statuscomment' => '変更要求を起票した状態。'],
        ['junban' => 2, 'statusname' => '調査中', 'percent' => 20, 'statuscomment' => '影響範囲・工数を調査中。'],
        ['junban' => 3, 'statusname' => '判定待ち', 'percent' => 40, 'statuscomment' => '実施可否の判定待ち。'],
        ['junban' => 4, 'statusname' => '承認待ち', 'percent' => 60, 'statuscomment' => '最終承認待ち。'],
        ['junban' => 5, 'statusname' => '対応中', 'percent' => 80, 'statuscomment' => '変更を実施中。'],
        ['junban' => 6, 'statusname' => '完了', 'percent' => 100, 'statuscomment' => '変更対応が完了。'],
        ['junban' => 7, 'statusname' => '却下', 'percent' => -2, 'statuscomment' => '変更要求を却下。'],
        ['junban' => 8, 'statusname' => '保留', 'percent' => -1, 'statuscomment' => '一時保留。'],
    ];

    public function up(): void
    {
        $siteIds = DB::table('rooms')->pluck('site_id');

        foreach ($siteIds as $siteId) {
            foreach ($this->rows as $row) {
                $exists = DB::table('statuses')
                    ->where('site_id', $siteId)
                    ->where('kind', 'change')
                    ->where('junban', $row['junban'])
                    ->exists();

                if (! $exists) {
                    DB::table('statuses')->insert($row + ['kind' => 'change', 'site_id' => $siteId]);
                }
            }
        }
    }

    public function down(): void
    {
        DB::table('statuses')->where('kind', 'change')->delete();
    }
};
