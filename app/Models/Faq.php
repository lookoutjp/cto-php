<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSite;

class Faq extends Model
{
    use BelongsToSite;

    protected $table = 'faqs';
    public $timestamps = false;
    protected $guarded = [];
}
