<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileItem extends Model
{
    protected $table = 'files';
    public $timestamps = false;
    protected $guarded = [];
}
