<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyChoice extends Model
{
    protected $table = 'survey_choices';
    public $timestamps = false;
    protected $guarded = [];
}
