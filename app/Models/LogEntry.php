<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Model;

class LogEntry extends Model
{
    use BelongsToSite;

    protected $table = 'logs';

    public $timestamps = false;

    protected $guarded = [];
}
