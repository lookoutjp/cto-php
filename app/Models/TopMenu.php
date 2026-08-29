<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSite;

class TopMenu extends Model
{
    use BelongsToSite;

    protected $table = 'top_menus';
    public $timestamps = false;
    protected $guarded = [];
}
