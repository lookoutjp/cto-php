<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Model;

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
