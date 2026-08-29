<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guestbook extends Model
{
    protected $table = 'guestbooks';
    public $timestamps = false;
    protected $guarded = [];
}
