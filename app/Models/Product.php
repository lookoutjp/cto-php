<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSite;

class Product extends Model
{
    use BelongsToSite;

    protected $table = 'products';
    public $timestamps = false;
    protected $guarded = [];
}
