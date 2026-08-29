<?php

namespace App\Console\Commands;

use App\Models\Room;
use App\Support\CurrentSite;
use App\Support\RoutineWorkGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * 定例作業マスターから、直近 N 日ぶんの定例作業を全サイトぶん生成する。
 * cron 例: 毎日 0:10  ->  php artisan routinework:generate
 */
class GenerateRoutineWorkLists extends Command
{
    protected $signature = 'routinework:generate
        {--days=40 : 今日から何日先まで生成するか}
        {--site= : 特定サイトのみ（site_id）}';

    protected $description = '定例作業マスターから定例作業（routine_work_lists）を生成する';

    public function handle(CurrentSite $currentSite): int
    {
        $start = Carbon::today();
        $end = Carbon::today()->addDays(max(1, (int) $this->option('days')));

        $rooms = Room::query()
            ->when($this->option('site'), fn ($q) => $q->where('site_id', $this->option('site')))
            ->get()
            ->filter(fn (Room $r) => $r->hasFunction('routineworkfunction'));

        $total = 0;
        foreach ($rooms as $room) {
            $currentSite->set($room->site_id);
            $result = (new RoutineWorkGenerator($start, $end))->run();
            $total += $result['created'];
            $this->line("  {$room->site_id}: マスター {$result['masters']} → 作成 {$result['created']}");
        }

        $this->info("完了。合計 {$total} 件の定例作業を作成しました（{$start->toDateString()} 〜 {$end->toDateString()}）。");

        return self::SUCCESS;
    }
}
