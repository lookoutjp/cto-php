<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use App\Models\Concerns\HasAttachments;
use App\Models\Concerns\TaskModel;
use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    use BelongsToSite;
    use HasAttachments;
    use TaskModel;

    public static string $taskKind = 'todo';

    protected $table = 'todos';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'duedate' => 'datetime',
        'dotoday' => 'datetime',
        'renewdate' => 'datetime',
        'completion_date' => 'datetime',
    ];
}
