<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSite;

class MailList extends Model
{
    use BelongsToSite;

    protected $table = 'mail_lists';
    public $timestamps = false;
    protected $guarded = [];
}
