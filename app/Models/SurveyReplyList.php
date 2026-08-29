<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSite;

class SurveyReplyList extends Model
{
    use BelongsToSite;

    protected $table = 'survey_reply_lists';
    public $timestamps = false;
    protected $guarded = [];
}
