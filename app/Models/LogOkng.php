<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Model;

class LogOkng extends Model
{
    use BelongsToSite;

    protected $table = 'log_okngs';

    public $timestamps = false;

    protected $guarded = [];
}
