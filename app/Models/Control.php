<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Model;

class Control extends Model
{
    use BelongsToSite;

    protected $table = 'controls';

    public $timestamps = false;

    protected $guarded = [];
}
