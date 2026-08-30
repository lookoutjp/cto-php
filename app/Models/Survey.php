<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Survey extends Model
{
    use BelongsToSite;

    protected $table = 'surveys';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'open_yn' => 'boolean',
        'specify_yn' => 'boolean',
        'answer_due_date' => 'datetime',
    ];

    public function choices(): HasMany
    {
        return $this->hasMany(SurveyChoice::class, 'survey_id')->orderBy('choice_number');
    }

    public function results(): HasMany
    {
        return $this->hasMany(SurveyChoiceResult::class, 'survey_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(SurveyReplyList::class, 'survey_id');
    }

    public function scopeOpen(Builder $q): Builder
    {
        return $q->where('open_yn', true)
            ->where(fn ($w) => $w->where('delete_to', '!=', 1)->orWhereNull('delete_to'));
    }

    public function hasReplied(string $memberId): bool
    {
        return $this->replies()->where('member_id', $memberId)->exists();
    }

    public function isPastDue(): bool
    {
        return $this->answer_due_date !== null && $this->answer_due_date->isPast();
    }

    public function acceptsAnswersFrom(string $memberId): bool
    {
        return (bool) $this->open_yn && ! $this->isPastDue() && ! $this->hasReplied($memberId);
    }

    public function isMultiSelect(): bool
    {
        return (int) $this->selectable_numbers > 1;
    }

    /** choice_number => 得票数 */
    public function tally(): \Illuminate\Support\Collection
    {
        return $this->results()
            ->selectRaw('choice_number, count(*) as c')
            ->groupBy('choice_number')
            ->pluck('c', 'choice_number');
    }

    /**
     * 記名式（specify_yn）用。choice_number => 投票した会員（表示名）の一覧。
     *
     * @return \Illuminate\Support\Collection<int, \Illuminate\Support\Collection<int, string>>
     */
    public function tallyWithVoters(): \Illuminate\Support\Collection
    {
        return $this->results()
            ->with('member:member_id,name')
            ->orderBy('dt')
            ->get(['id', 'choice_number', 'member_id'])
            ->groupBy('choice_number')
            ->map(fn ($rows) => $rows
                ->map(fn ($r) => $r->member?->displayName() ?? $r->member_id)
                ->unique()
                ->values()
            );
    }

    public function respondentCount(): int
    {
        return $this->replies()->count();
    }
}
