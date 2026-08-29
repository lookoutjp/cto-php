<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebSession extends Model
{
    protected $table = 'web_sessions';
    protected $primaryKey = 'token';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = [];
}
