<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use BelongsToSite;

    protected $table = 'categories';

    public $timestamps = false;

    protected $guarded = [];
}
