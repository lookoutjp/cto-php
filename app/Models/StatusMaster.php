<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSite;

class StatusMaster extends Model
{
    use BelongsToSite;

    protected $table = 'statuses';
    public $timestamps = false;
    protected $guarded = [];
}
