<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteCustom extends Model
{
    protected $table = 'site_customs';
    protected $primaryKey = 'custname';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = [];
}
