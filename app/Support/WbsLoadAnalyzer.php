<?php

namespace App\Support;

use App\Models\Member;
use App\Models\Wbs;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * WBS のリソース負荷分析（簡易リソース平準化の第一歩）。
 *
 * 各リーフ WBS の所要日数（tododays）を、その期間 [start, end] の稼働日に
 * 均等配分し、担当者(person_do) × 週（ISO週）ごとに合計する。
 * 週あたりの稼働可能日数（既定 5）を超える週を「過負荷」として検出する。
 *
 * start/end は godate/duedate（無ければ start_date/complete_date）。
 * 期間が無いタスクは分析対象外。
 */
class WbsLoadAnalyzer
{
    public function __construct(
        private readonly Collection $allWbs,
        private readonly WorkCalendar $cal,
        private readonly float $weeklyCapacity = 5.0,
    ) {}

    public static function forCurrentSite(float $weeklyCapacity = 5.0): self
    {
        return new self(
            Wbs::query()->notDeleted()->get(),
            WorkCalendar::forCurrentSite('working'),
            $weeklyCapacity,
        );
    }

    /**
     * @return array{
     *   weeks: list<string>,
     *   rows: list<array{member_id:?string, name:string, cells:array<string,float>, total:float, overloaded:bool}>,
     *   overloads: list<array{name:string, week:string, load:float, tasks:list<array{id:int,title:string,days:float}>}>,
     *   capacity: float,
     * }
     */
    public function analyze(): array
    {
        $leaves = $this->allWbs
            ->filter(fn (Wbs $w) => ! $w->iscategory && (int) $w->tododays > 0)
            ->map(fn (Wbs $w) => $this->spanOf($w))
            ->filter();

        // member_id => weekKey => ['days' => float, 'tasks' => [id => days]]
        $load = [];
        $allWeeks = [];

        foreach ($leaves as $leaf) {
            $workdays = $this->workingDatesBetween($leaf['start'], $leaf['end']);
            if ($workdays === []) {
                continue;
            }
            $perDay = $leaf['days'] / count($workdays);
            $mid = $leaf['member_id'] ?? '__none__';

            foreach ($workdays as $d) {
                $wk = $this->weekKey($d);
                $allWeeks[$wk] = true;
                $load[$mid][$wk]['days'] = ($load[$mid][$wk]['days'] ?? 0) + $perDay;
                $load[$mid][$wk]['tasks'][$leaf['id']] = ($load[$mid][$wk]['tasks'][$leaf['id']] ?? 0) + $perDay;
                $load[$mid][$wk]['titles'][$leaf['id']] = $leaf['title'];
            }
        }

        ksort($allWeeks);
        $weeks = array_keys($allWeeks);

        $names = Member::query()
            ->whereIn('member_id', array_filter(array_keys($load), fn ($k) => $k !== '__none__'))
            ->pluck('name', 'member_id');

        $rows = [];
        $overloads = [];

        foreach ($load as $mid => $byWeek) {
            $name = $mid === '__none__' ? '（担当者未設定）' : ($names[$mid] ?? $mid);
            $cells = [];
            $total = 0.0;
            $overloaded = false;

            foreach ($weeks as $wk) {
                $days = round($byWeek[$wk]['days'] ?? 0, 1);
                $cells[$wk] = $days;
                $total += $days;

                if ($days > $this->weeklyCapacity + 0.05) {
                    $overloaded = true;
                    $tasks = [];
                    foreach ($byWeek[$wk]['tasks'] as $tid => $td) {
                        $tasks[] = ['id' => $tid, 'title' => $byWeek[$wk]['titles'][$tid], 'days' => round($td, 1)];
                    }
                    usort($tasks, fn ($a, $b) => $b['days'] <=> $a['days']);
                    $overloads[] = ['name' => $name, 'week' => $wk, 'load' => $days, 'tasks' => $tasks];
                }
            }

            $rows[] = [
                'member_id' => $mid === '__none__' ? null : $mid,
                'name' => $name,
                'cells' => $cells,
                'total' => round($total, 1),
                'overloaded' => $overloaded,
            ];
        }

        usort($rows, fn ($a, $b) => [$b['overloaded'], $b['total']] <=> [$a['overloaded'], $a['total']]);

        return [
            'weeks' => $weeks,
            'rows' => $rows,
            'overloads' => $overloads,
            'capacity' => $this->weeklyCapacity,
        ];
    }

    /** @return array{id:int,title:string,member_id:?string,start:Carbon,end:Carbon,days:float}|null */
    private function spanOf(Wbs $w): ?array
    {
        $start = $w->godate ?? $w->start_date;
        $end = $w->duedate ?? $w->complete_date;

        if (! $start || ! $end) {
            return null;
        }
        $start = $start->copy()->startOfDay();
        $end = $end->copy()->startOfDay();
        if ($end->lt($start)) {
            [$start, $end] = [$end, $start];
        }

        return [
            'id' => (int) $w->id,
            'title' => (string) $w->title,
            'member_id' => $w->person_do ?: null,
            'start' => $start,
            'end' => $end,
            'days' => (float) max(1, (int) $w->tododays),
        ];
    }

    /** @return list<Carbon> */
    private function workingDatesBetween(Carbon $start, Carbon $end): array
    {
        $out = [];
        $d = $start->copy();
        $guard = 0;
        while ($d->lte($end) && $guard++ < 3650) {
            if ($this->cal->isWorkingDay($d)) {
                $out[] = $d->copy();
            }
            $d->addDay();
        }

        return $out;
    }

    private function weekKey(Carbon $d): string
    {
        return $d->isoFormat('GGGG-[W]WW');
    }
}
