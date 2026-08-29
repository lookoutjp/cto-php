<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SysVersion extends Model
{
    protected $table = 'sysversions';
    public $timestamps = false;
    protected $guarded = [];
}
