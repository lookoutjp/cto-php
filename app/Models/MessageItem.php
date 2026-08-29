<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageItem extends Model
{
    protected $table = 'messages';
    public $timestamps = false;
    protected $guarded = [];
}
