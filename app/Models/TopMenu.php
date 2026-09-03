<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Model;

class TopMenu extends Model
{
    use BelongsToSite;

    protected $table = 'top_menus';

    public $timestamps = false;

    protected $guarded = [];
}
