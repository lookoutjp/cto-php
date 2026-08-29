<?php

namespace App\Support;

use App\Models\Holiday;
use Illuminate\Support\Carbon;

/**
 * 稼働日カレンダー。
 *   mode = 'calendar' … すべての日を稼働日として扱う（暦日ベース）
 *   mode = 'working'  … 土日 + サイト休日(holidays) を非稼働日として除外
 */
class WorkCalendar
{
    /** @var array<string, bool> 'Y-m-d' => true */
    private array $holidays = [];

    public function __construct(
        public readonly string $mode = 'working',
        iterable $holidayDates = [],
    ) {
        foreach ($holidayDates as $d) {
            $this->holidays[Carbon::parse($d)->toDateString()] = true;
        }
    }

    public static function forCurrentSite(string $mode = 'working'): self
    {
        return new self($mode, Holiday::query()->pluck('date')->all());
    }

    public function isWorkingDay(Carbon $day): bool
    {
        if ($this->mode === 'calendar') {
            return true;
        }

        return ! $day->isWeekend() && ! isset($this->holidays[$day->toDateString()]);
    }

    /** その日以降で最初の稼働日（その日が稼働日ならその日）。 */
    public function nextWorkingDay(Carbon $day): Carbon
    {
        $d = $day->copy()->startOfDay();
        $guard = 0;
        while (! $this->isWorkingDay($d) && $guard++ < 3650) {
            $d->addDay();
        }

        return $d;
    }

    /**
     * $from から稼働日ベースで $n 日進めた日付。
     * $n = 0 なら $from を稼働日に丸めた日。$n < 0 は過去方向。
     */
    public function addWorkingDays(Carbon $from, int $n): Carbon
    {
        $d = $this->nextWorkingDayInDirection($from->copy()->startOfDay(), $n >= 0 ? 1 : -1);
        $step = $n >= 0 ? 1 : -1;
        $remaining = abs($n);
        $guard = 0;

        while ($remaining > 0 && $guard++ < 36500) {
            $d->addDays($step);
            if ($this->isWorkingDay($d)) {
                $remaining--;
            }
        }

        return $d;
    }

    private function nextWorkingDayInDirection(Carbon $day, int $dir): Carbon
    {
        $d = $day->copy();
        $guard = 0;
        while (! $this->isWorkingDay($d) && $guard++ < 3650) {
            $d->addDays($dir);
        }

        return $d;
    }

    /** $from から $to まで（両端含む）の稼働日数 - 1。$to が $from より前なら負。 */
    public function workingDaysBetween(Carbon $from, Carbon $to): int
    {
        if ($from->toDateString() === $to->toDateString()) {
            return 0;
        }

        $dir = $to->gt($from) ? 1 : -1;
        $a = $this->addWorkingDays($from, 0);
        $b = $this->addWorkingDays($to, 0);
        $count = 0;
        $d = $a->copy();
        $guard = 0;
        while ($d->toDateString() !== $b->toDateString() && $guard++ < 36500) {
            $d->addDays($dir);
            if ($this->isWorkingDay($d)) {
                $count += $dir;
            }
        }

        return $count;
    }
}
