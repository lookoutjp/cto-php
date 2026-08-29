<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Survey extends Model
{
    protected $table = 'surveys';
    public $timestamps = false;
    protected $guarded = [];

    public function choices(): HasMany
    {
        return $this->hasMany(SurveyChoice::class, 'survey_id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(SurveyChoiceResult::class, 'survey_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(SurveyReplyList::class, 'survey_id');
    }
}
