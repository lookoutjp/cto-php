<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsItem extends Model
{
    protected $table = 'news';
    public $timestamps = false;
    protected $guarded = [];
}
