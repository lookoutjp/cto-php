<?php

namespace App\Support;

use App\Models\RoutineWork;
use App\Models\RoutineWorkList;
use App\Models\StatusMaster;
use Illuminate\Support\Carbon;

/**
 * 定例作業マスター（routine_works）の繰り返しルールから、指定期間の
 * 定例作業（routine_work_lists）を生成する。旧ASP: RoutineWorkMake.asp /
 * include/SQLS.asp の RoutineWork_make。
 *
 * circle:
 *   day   … 毎日
 *   week  … circle_number は曜日（旧VBScript Weekday: 1=日 … 7=土）
 *   month … circle_number は日（1〜31）
 *   year  … circle_number は "M/D"（例 "11/4"）
 *
 * 対象日は [start,end] ∩ [master.godate, master.duedate]。
 * 同一マスター×同一 actiondate の行が既にあれば作らない（重複防止）。
 */
class RoutineWorkGenerator
{
    public function __construct(
        private readonly Carbon $start,
        private readonly Carbon $end,
    ) {}

    /** @return array{masters:int, created:int} */
    public function run(): array
    {
        $start = $this->start->copy()->startOfDay();
        $end = $this->end->copy()->startOfDay();

        $newStatusId = StatusMaster::query()
            ->whereRaw('lower(kind) = ?', ['routinework'])
            ->where('percent', 0)
            ->orderBy('junban')
            ->value('id');

        $masters = RoutineWork::query()
            ->where(fn ($q) => $q->where('delete_to', '!=', 1)->orWhereNull('delete_to'))
            ->whereNotNull('godate')
            ->whereNotNull('duedate')
            ->whereNotNull('circle')
            ->whereDate('godate', '<=', $end)
            ->whereDate('duedate', '>=', $start)
            ->get();

        $created = 0;

        foreach ($masters as $m) {
            $from = $start->greaterThan($m->godate) ? $start->copy() : Carbon::parse($m->godate)->startOfDay();
            $to = $end->lessThan($m->duedate) ? $end->copy() : Carbon::parse($m->duedate)->startOfDay();

            $existing = RoutineWorkList::query()
                ->where('routine_work_id', $m->id)
                ->whereNotNull('actiondate')
                ->pluck('actiondate')
                ->map(fn ($d) => Carbon::parse($d)->toDateString())
                ->flip();

            for ($day = $from->copy(); $day->lte($to); $day->addDay()) {
                if (! $this->matchesDate((string) $m->circle, $m->circle_number, $day)) {
                    continue;
                }
                if ($existing->has($day->toDateString())) {
                    continue;
                }

                $this->createRow($m, $day, $newStatusId);
                $existing->put($day->toDateString(), true);
                $created++;
            }
        }

        return ['masters' => $masters->count(), 'created' => $created];
    }

    private function matchesDate(string $circle, mixed $number, Carbon $day): bool
    {
        return match ($circle) {
            'day' => true,
            // 旧VBScript Weekday: 1=日曜 … 7=土曜。Carbon dayOfWeek: 0=日 … 6=土。
            'week' => (int) $number === $day->dayOfWeek + 1,
            'month' => (int) $number === $day->day,
            'year' => trim((string) $number) === $day->month.'/'.$day->day,
            default => false,
        };
    }

    private function createRow(RoutineWork $m, Carbon $day, ?int $statusId): void
    {
        $row = new RoutineWorkList;
        $row->routine_work_id = $m->id;
        $row->maker = $m->maker;
        $row->title = $m->title;
        $row->category = $m->category;
        $row->content = $m->content;
        $row->person_do = $m->person_do;
        $row->team_id = $m->team_id;
        $row->completioncriteria = $m->completioncriteria;
        $row->approver = $m->approver;
        $row->circle = $m->circle;
        $row->hours_et = $m->hours_et;
        $row->actiondate = $day->copy();
        $row->status = $statusId;
        $row->renewdate = now();
        $row->delete_to = 0;
        $row->save(); // BelongsToSite が site_id をセット
    }
}
