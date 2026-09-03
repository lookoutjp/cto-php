<?php

namespace App\Console\Commands;

use App\Models\Member;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * 一定時間アクセスの無い会員の online フラグを 0 に戻す（旧ASP onlinechk.asp の 20 分掃除）。
 * オンライン判定自体は Member::isOnline()（timerenew ベース）で行うが、
 * 管理画面の表示用に int カラムも整合させておく。
 *   php artisan members:sweep-online
 */
class SweepOfflineMembers extends Command
{
    protected $signature = 'members:sweep-online';

    protected $description = '最終アクセスが古い会員の online フラグを 0 に戻す';

    public function handle(): int
    {
        $cutoff = now()->subMinutes(Member::PRESENCE_MINUTES);

        $n = DB::table('members')
            ->where('online', 1)
            ->where(fn ($q) => $q->where('timerenew', '<', $cutoff)->orWhereNull('timerenew'))
            ->update(['online' => 0]);

        $this->info("offline に戻した会員: {$n} 件");

        return self::SUCCESS;
    }
}
