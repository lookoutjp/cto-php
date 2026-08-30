<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use App\Models\Concerns\HasAttachments;
use App\Models\Concerns\TaskModel;
use Illuminate\Database\Eloquent\Model;

class ChangeRequest extends Model
{
    use BelongsToSite;
    use HasAttachments;
    use TaskModel;

    public static string $taskKind = 'change';
    public static ?string $taskDateColumn = 'duedate';

    protected $table = 'change_requests';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'duedate' => 'datetime',
        'dotoday' => 'datetime',
        'renewdate' => 'datetime',
        'occurrence_day' => 'datetime',
        'judge_day' => 'datetime',
        'done_day' => 'datetime',
    ];
}
