<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSite;

class SurveyChoiceResult extends Model
{
    use BelongsToSite;

    protected $table = 'survey_choice_results';
    public $timestamps = false;
    protected $guarded = [];
}
