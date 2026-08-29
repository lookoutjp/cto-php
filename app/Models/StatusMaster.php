<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusMaster extends Model
{
    protected $table = 'statuses';
    public $timestamps = false;
    protected $guarded = [];
}
