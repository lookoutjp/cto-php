<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSite;

class ChangeRequest extends Model
{
    use BelongsToSite;

    protected $table = 'change_requests';
    public $timestamps = false;
    protected $guarded = [];
}
