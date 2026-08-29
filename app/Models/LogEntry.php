<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSite;

class LogEntry extends Model
{
    use BelongsToSite;

    protected $table = 'logs';
    public $timestamps = false;
    protected $guarded = [];
}
