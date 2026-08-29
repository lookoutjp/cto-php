<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSite;

class Guestbook extends Model
{
    use BelongsToSite;

    protected $table = 'guestbooks';
    public $timestamps = false;
    protected $guarded = [];
}
