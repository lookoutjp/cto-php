<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSite;

class ContentComment extends Model
{
    use BelongsToSite;

    protected $table = 'content_comments';
    public $timestamps = false;
    protected $guarded = [];
}
