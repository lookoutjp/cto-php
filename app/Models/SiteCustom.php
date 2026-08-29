<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSite;

class SiteCustom extends Model
{
    use BelongsToSite;

    protected $table = 'site_customs';
    protected $primaryKey = 'custname';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = [];
}
