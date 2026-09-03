<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Model;

/**
 * サイトごとの休日（稼働日カレンダーの非稼働日）。土日は WorkCalendar 側で固定除外。
 */
class Holiday extends Model
{
    use BelongsToSite;

    protected $table = 'holidays';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
    ];
}
