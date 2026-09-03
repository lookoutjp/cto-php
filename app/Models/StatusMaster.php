<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Model;

class StatusMaster extends Model
{
    use BelongsToSite;

    protected $table = 'statuses';

    public $timestamps = false;

    protected $guarded = [];
}
