<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSite;

class NewsItem extends Model
{
    use BelongsToSite;

    protected $table = 'news';
    public $timestamps = false;
    protected $guarded = [];
}
