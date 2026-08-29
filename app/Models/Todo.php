<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSite;

class Todo extends Model
{
    use BelongsToSite;

    protected $table = 'todos';
    public $timestamps = false;
    protected $guarded = [];
}
