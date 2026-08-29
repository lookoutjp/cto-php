<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuestbookCategory extends Model
{
    protected $table = 'guestbook_categories';
    public $timestamps = false;
    protected $guarded = [];
}
