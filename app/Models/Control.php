<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSite;

class Control extends Model
{
    use BelongsToSite;

    protected $table = 'controls';
    public $timestamps = false;
    protected $guarded = [];
}
