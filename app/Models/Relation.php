<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Relation extends Model
{
    protected $table = 'relations';
    public $timestamps = false;
    protected $guarded = [];
}
