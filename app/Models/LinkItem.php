<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSite;

class LinkItem extends Model
{
    use BelongsToSite;

    protected $table = 'links';
    public $timestamps = false;
    protected $guarded = [];
}
