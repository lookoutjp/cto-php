<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use App\Models\Concerns\TaskModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoutineWorkList extends Model
{
    use BelongsToSite;
    use TaskModel;

    public static string $taskKind = 'routinework';
    public static ?string $taskDateColumn = 'actiondate'; // 定例作業は実施日ベース

    protected $table = 'routine_work_lists';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'actiondate' => 'datetime',
        'acteddate' => 'datetime',
        'dotoday' => 'datetime',
        'renewdate' => 'datetime',
        'completion_date' => 'datetime',
        'add_date_time' => 'datetime',
    ];

    public function routineWork(): BelongsTo
    {
        return $this->belongsTo(RoutineWork::class, 'routine_work_id');
    }
}
