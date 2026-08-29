<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSite;

class GuestbookCategory extends Model
{
    use BelongsToSite;

    protected $table = 'guestbook_categories';
    public $timestamps = false;
    protected $guarded = [];
}
