<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSite;

class Category extends Model
{
    use BelongsToSite;

    protected $table = 'categories';
    public $timestamps = false;
    protected $guarded = [];
}
