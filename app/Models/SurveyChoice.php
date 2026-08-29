<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSite;

class SurveyChoice extends Model
{
    use BelongsToSite;

    protected $table = 'survey_choices';
    public $timestamps = false;
    protected $guarded = [];
}
