<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSite;

class Inquiry extends Model
{
    use BelongsToSite;

    protected $table = 'inquiries';
    public $timestamps = false;
    protected $guarded = [];
}
