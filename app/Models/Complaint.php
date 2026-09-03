<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    use BelongsToSite;

    protected $table = 'complaints';

    public $timestamps = false;

    protected $guarded = [];
}
