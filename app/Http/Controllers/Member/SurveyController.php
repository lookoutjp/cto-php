<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Survey;
use App\Models\SurveyChoiceResult;
use App\Models\SurveyReplyList;
use App\Support\CurrentSite;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * 会員向けサーベイ（アンケート）。旧 SurveyList_My.asp / Survey.asp 相当。
 */
class SurveyController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureEnabled();

        $memberId = $request->user()->getKey();

        $surveys = Survey::query()->open()
            ->withCount('choices')
            ->orderByDesc('id')
            ->get()
            ->filter(fn (Survey $s) => $s->choices_count > 0);

        $repliedIds = SurveyReplyList::query()
            ->where('member_id', $memberId)
            ->pluck('survey_id')->flip();

        return view('member.survey-index', compact('surveys', 'repliedIds'));
    }

    public function show(Request $request, int $id): View
    {
        $this->ensureEnabled();

        $memberId = $request->user()->getKey();
        $survey = Survey::query()->open()->with('choices')->findOrFail($id);

        $canAnswer = $survey->acceptsAnswersFrom($memberId);

        return view('member.survey-show', [
            'survey' => $survey,
            'canAnswer' => $canAnswer,
            'hasReplied' => $survey->hasReplied($memberId),
            'tally' => $canAnswer ? collect() : $survey->tally(),
        ]);
    }

    public function answer(Request $request, int $id): RedirectResponse
    {
        $this->ensureEnabled();

        $memberId = $request->user()->getKey();
        $survey = Survey::query()->open()->with('choices')->findOrFail($id);

        if (! $survey->acceptsAnswersFrom($memberId)) {
            throw ValidationException::withMessages(['choices' => 'この設問には回答できません。']);
        }

        $validChoiceNumbers = $survey->choices->pluck('choice_number')->all();
        $max = max(1, (int) $survey->selectable_numbers);

        $data = $request->validate([
            'choices' => ['required', 'array', 'min:1', "max:{$max}"],
            'choices.*' => ['integer', 'in:'.implode(',', $validChoiceNumbers)],
        ], [], ['choices' => '選択肢']);

        DB::transaction(function () use ($survey, $memberId, $data) {
            foreach (array_unique($data['choices']) as $choiceNumber) {
                $r = new SurveyChoiceResult;
                $r->survey_id = $survey->id;
                $r->member_id = $memberId;
                $r->choice_number = $choiceNumber;
                $r->dt = now();
                $r->save(); // BelongsToSite が site_id をセット
            }

            $reply = new SurveyReplyList;
            $reply->survey_id = $survey->id;
            $reply->member_id = $memberId;
            $reply->save();
        });

        return redirect()->route('surveys.show', $survey->id)
            ->with('status', '回答を送信しました。');
    }

    private function ensureEnabled(): void
    {
        if (! Room::find(app(CurrentSite::class)->id())?->hasFunction('surveyfunction')) {
            throw new NotFoundHttpException;
        }
    }
}
