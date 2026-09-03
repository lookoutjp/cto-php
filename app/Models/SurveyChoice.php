<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Model;

class SurveyChoice extends Model
{
    use BelongsToSite;

    protected $table = 'survey_choices';

    public $timestamps = false;

    protected $guarded = [];
}
