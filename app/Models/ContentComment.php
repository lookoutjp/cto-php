<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentComment extends Model
{
    protected $table = 'content_comments';
    public $timestamps = false;
    protected $guarded = [];
}
