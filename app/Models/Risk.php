<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSite;

class Risk extends Model
{
    use BelongsToSite;

    protected $table = 'risks';
    public $timestamps = false;
    protected $guarded = [];
}
