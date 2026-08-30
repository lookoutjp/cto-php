<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use App\Models\Concerns\HasAttachments;
use App\Models\Concerns\TaskModel;
use Illuminate\Database\Eloquent\Model;

class Problem extends Model
{
    use BelongsToSite;
    use HasAttachments;
    use TaskModel;

    public static string $taskKind = 'problem';

    protected $table = 'problems';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'duedate' => 'datetime',
        'dotoday' => 'datetime',
        'renewdate' => 'datetime',
        'completion_date' => 'datetime',
    ];
}
