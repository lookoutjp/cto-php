<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSite;

class FileItem extends Model
{
    use BelongsToSite;

    protected $table = 'files';
    public $timestamps = false;
    protected $guarded = [];
}
