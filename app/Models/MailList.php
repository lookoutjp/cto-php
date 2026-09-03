<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Model;

class MailList extends Model
{
    use BelongsToSite;

    protected $table = 'mail_lists';

    public $timestamps = false;

    protected $guarded = [];
}
