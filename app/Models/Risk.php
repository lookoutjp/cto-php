<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use App\Models\Concerns\TaskModel;
use Illuminate\Database\Eloquent\Model;

class Risk extends Model
{
    use BelongsToSite;
    use TaskModel;

    public static string $taskKind = 'risk';

    protected $table = 'risks';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'duedate' => 'datetime',
        'dotoday' => 'datetime',
        'renewdate' => 'datetime',
        'completion_date' => 'datetime',
    ];
}
