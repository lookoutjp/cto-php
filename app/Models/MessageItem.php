<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSite;

class MessageItem extends Model
{
    use BelongsToSite;

    protected $table = 'messages';
    public $timestamps = false;
    protected $guarded = [];
}
