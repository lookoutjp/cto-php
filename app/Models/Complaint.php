<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSite;

class Complaint extends Model
{
    use BelongsToSite;

    protected $table = 'complaints';
    public $timestamps = false;
    protected $guarded = [];
}
