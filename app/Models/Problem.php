<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSite;

class Problem extends Model
{
    use BelongsToSite;

    protected $table = 'problems';
    public $timestamps = false;
    protected $guarded = [];
}
