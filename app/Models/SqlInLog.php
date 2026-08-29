<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SqlInLog extends Model
{
    protected $table = 'sql_in_logs';
    protected $primaryKey = 'sql_in_id';
    public $timestamps = false;
    protected $guarded = [];
}
