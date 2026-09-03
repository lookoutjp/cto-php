<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Model;

class SurveyReplyList extends Model
{
    use BelongsToSite;

    protected $table = 'survey_reply_lists';

    public $timestamps = false;

    protected $guarded = [];
}
