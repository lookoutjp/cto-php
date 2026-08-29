<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSite;

class RoutineWorkList extends Model
{
    use BelongsToSite;

    protected $table = 'routine_work_lists';
    public $timestamps = false;
    protected $guarded = [];
}
