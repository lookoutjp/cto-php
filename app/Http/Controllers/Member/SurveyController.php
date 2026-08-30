<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Room;
use App\Models\Survey;
use App\Models\SurveyChoice;
use App\Models\SurveyChoiceResult;
use App\Models\SurveyReplyList;
use App\Support\CurrentSite;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * 会員向けサーベイ（アンケート）。旧 SurveyList_My.asp / Survey.asp（回答）
 * ＋ SurveyList_Mytask.asp / Survey_new.asp / Surveyedit_son.asp（作成・編集）相当。
 *
 * 回答は全プロジェクト参加者。作成・編集・締切は「自分が作ったサーベイ」＋管理員。
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

        // 締切済み・受付終了のサーベイも集計結果は閲覧できる（回答は open() のときだけ）。
        $survey = Survey::query()
            ->where(fn ($q) => $q->where('delete_to', '!=', 1)->orWhereNull('delete_to'))
            ->with('choices')
            ->findOrFail($id);

        $canAnswer = $survey->acceptsAnswersFrom($memberId);

        $resultsVisible = ! $canAnswer;

        return view('member.survey-show', [
            'survey' => $survey,
            'canAnswer' => $canAnswer,
            'hasReplied' => $survey->hasReplied($memberId),
            'tally' => $resultsVisible ? $survey->tally() : collect(),
            'voters' => $resultsVisible && $survey->specify_yn ? $survey->tallyWithVoters() : collect(),
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

    // ---- 作成・編集（自分のサーベイ＋管理員）----

    public function manage(Request $request): View
    {
        $this->ensureEnabled();

        $surveys = Survey::query()
            ->where(fn ($q) => $q->where('delete_to', '!=', 1)->orWhereNull('delete_to'))
            ->when(! $this->isManager($request), fn ($q) => $q->where('member_id', $request->user()->getKey()))
            ->withCount(['choices', 'replies'])
            ->orderByDesc('id')
            ->get();

        return view('member.survey-manage', compact('surveys'));
    }

    public function create(): View
    {
        $this->ensureEnabled();

        return view('member.survey-form', [
            'survey' => new Survey(['selectable_numbers' => 1, 'open_yn' => true, 'specify_yn' => false]),
            'choices' => collect(),
            'lockChoices' => false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureEnabled();

        $data = $this->validatedSurvey($request);

        $survey = new Survey;
        $this->fillSurvey($survey, $data);
        $survey->member_id = $request->user()->getKey();
        $survey->save();

        $this->syncChoices($survey, $data['choices']);

        return redirect()->route('surveys.manage')->with('status', 'サーベイを作成しました。');
    }

    public function edit(Request $request, int $id): View
    {
        $survey = $this->findOwn($request, $id);
        $survey->load('choices');

        return view('member.survey-form', [
            'survey' => $survey,
            'choices' => $survey->choices,
            'lockChoices' => $survey->replies()->exists(),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $survey = $this->findOwn($request, $id);
        $locked = $survey->replies()->exists();

        $data = $this->validatedSurvey($request, $locked);
        $this->fillSurvey($survey, $data);
        $survey->save();

        if (! $locked) {
            $this->syncChoices($survey, $data['choices']);
        }

        return redirect()->route('surveys.manage')->with('status', 'サーベイを更新しました。');
    }

    /** 受付の締切／再開（旧: open_yn の切替）。 */
    public function toggleOpen(Request $request, int $id): RedirectResponse
    {
        $survey = $this->findOwn($request, $id);
        $survey->open_yn = ! $survey->open_yn;
        $survey->save();

        return back()->with('status', $survey->open_yn ? '受付を再開しました。' : '受付を締め切りました。');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $survey = $this->findOwn($request, $id);
        $survey->delete_to = 1;
        $survey->save();

        return redirect()->route('surveys.manage')->with('status', 'サーベイを削除しました。');
    }

    // ---- helpers ----

    private function validatedSurvey(Request $request, bool $lockChoices = false): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'selectable_numbers' => ['required', 'integer', 'min:1', 'max:20'],
            'answer_due_date' => ['nullable', 'date'],
            'open_yn' => ['nullable', 'boolean'],
            'specify_yn' => ['nullable', 'boolean'],
        ];

        if (! $lockChoices) {
            $rules['choices'] = ['required', 'array', 'min:2', 'max:20'];
            $rules['choices.*.title'] = ['required', 'string', 'max:255'];
            $rules['choices.*.explain'] = ['nullable', 'string', 'max:2000'];
        }

        $data = $request->validate($rules, [], [
            'title' => 'タイトル', 'selectable_numbers' => '選択可能数',
            'answer_due_date' => '回答期限', 'choices' => '選択肢',
            'choices.*.title' => '選択肢', 'choices.*.explain' => '選択肢の説明',
        ]);

        $data['choices'] ??= [];

        return $data;
    }

    private function fillSurvey(Survey $survey, array $data): void
    {
        $survey->fill(Arr::only($data, ['title', 'selectable_numbers']));
        $survey->open_yn = (bool) ($data['open_yn'] ?? false);
        $survey->specify_yn = (bool) ($data['specify_yn'] ?? false);
        $survey->answer_due_date = filled($data['answer_due_date'] ?? null)
            ? Carbon::parse($data['answer_due_date'])->endOfDay()
            : null;
    }

    private function syncChoices(Survey $survey, array $choices): void
    {
        DB::transaction(function () use ($survey, $choices) {
            SurveyChoice::query()->where('survey_id', $survey->id)->delete();

            $n = 1;
            foreach ($choices as $c) {
                if (blank($c['title'] ?? null)) {
                    continue;
                }
                $row = new SurveyChoice;
                $row->survey_id = $survey->id;
                $row->choice_number = $n++;
                $row->choice_title = $c['title'];
                $row->choice_explain = $c['explain'] ?? null;
                $row->save();
            }
        });
    }

    private function findOwn(Request $request, int $id): Survey
    {
        $this->ensureEnabled();

        $survey = Survey::query()
            ->where(fn ($q) => $q->where('delete_to', '!=', 1)->orWhereNull('delete_to'))
            ->findOrFail($id);

        abort_unless(
            $this->isManager($request) || (string) $survey->member_id === (string) $request->user()->getKey(),
            403,
            'このサーベイを編集する権限がありません。'
        );

        return $survey;
    }

    private function isManager(Request $request): bool
    {
        $user = $request->user();

        return $user instanceof Member
            && ($user->isSuperAdmin() || $user->managesSite(app(CurrentSite::class)->id()));
    }

    private function ensureEnabled(): void
    {
        if (! Room::find(app(CurrentSite::class)->id())?->hasFunction('surveyfunction')) {
            throw new NotFoundHttpException;
        }
    }
}
