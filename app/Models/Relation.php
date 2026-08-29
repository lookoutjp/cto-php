<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSite;

class Relation extends Model
{
    use BelongsToSite;

    protected $table = 'relations';
    public $timestamps = false;
    protected $guarded = [];
}
