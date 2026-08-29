<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChangeRequest extends Model
{
    protected $table = 'change_requests';
    public $timestamps = false;
    protected $guarded = [];
}
