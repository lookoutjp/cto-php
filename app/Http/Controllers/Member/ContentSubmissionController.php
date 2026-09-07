<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\ContentSort;
use App\Models\ContentSortManager;
use App\Models\Member;
use App\Support\RichText;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * 会員のコンテンツ投稿（審査待ち → カテゴリ管理員の承認で公開）と、
 * 「カテゴリ管理員になる」申請。
 *
 * 承認・却下ができるのは Member::managesCategory()（スーパー管理員 / サイト管理員 /
 * そのカテゴリ・祖先のカテゴリ管理員）。
 * 申請の承認・直接指定はスーパー管理員（Filament: ContentSortManagerResource）。
 */
class ContentSubmissionController extends Controller
{
    /** 投稿フォーム（新規 or 自分の下書き編集 ?edit=）。 */
    public function submitForm(Request $request, int $category): View
    {
        $member = $this->member();
        $cat = $this->visibleCategory($category);

        $editing = null;
        if ($editId = $request->integer('edit')) {
            $editing = Content::query()->where('id', $editId)
                ->where('member_id', $member->getKey())
                ->whereIn('ok', [0, 2])
                ->first();
        }

        return view('member.content-submit', [
            'category' => $cat,
            'editing' => $editing,
            'canPublishDirectly' => $member->managesCategory($cat),
        ]);
    }

    public function submit(Request $request, int $category): RedirectResponse
    {
        $member = $this->member();
        $cat = $this->visibleCategory($category);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'explain' => ['nullable', 'string', 'max:100000'],
            'edit' => ['nullable', 'integer'],
        ], [], ['name' => 'タイトル', 'explain' => '本文']);

        $body = RichText::clean($data['explain'] ?? null);
        if ($body === null) {
            throw ValidationException::withMessages(['explain' => '本文を入力してください。']);
        }

        $directPublish = $member->managesCategory($cat);

        $content = null;
        if (! empty($data['edit'])) {
            $content = Content::query()->where('id', $data['edit'])
                ->where('member_id', $member->getKey())
                ->whereIn('ok', [0, 2])
                ->first();
        }

        if (! $content) {
            $content = new Content;
            $content->content_sort = $cat->id;
            $content->member_id = $member->getKey();
            $content->owner = $member->getKey();
            $content->adddatetime = now();
            $content->junban = 0;
            $content->clicks = 0;
        }

        $content->name = $data['name'];
        $content->explain = $body;
        $content->review_note = null;

        if ($directPublish) {
            $content->ok = 1;
            $content->oktime = now();
        } else {
            $content->ok = 2; // 審査待ち
        }
        $content->save(); // BelongsToSite が site_id をセット

        if ($directPublish) {
            return redirect()->route('contents.show', $content)->with('status', '投稿を公開しました。');
        }

        return redirect()->route('contents.mine')
            ->with('status', '投稿しました。カテゴリ管理員の承認をお待ちください。');
    }

    /** 自分の投稿一覧（審査待ち / 公開 / 差し戻し）。 */
    public function mySubmissions(Request $request): View
    {
        $member = $this->member();

        $items = Content::query()
            ->where('member_id', $member->getKey())
            ->with('sort:id,name')
            ->orderByDesc('adddatetime')->orderByDesc('id')
            ->paginate(20);

        return view('member.content-mine', ['items' => $items]);
    }

    /** 自分が管理するカテゴリの、承認待ち投稿一覧。 */
    public function review(Request $request): View
    {
        $member = $this->member();
        $manageableIds = ContentSort::manageableIdsFor($member);

        $filterCategory = $request->integer('category') ?: null;

        $items = Content::query()->pendingReview()
            ->whereIn('content_sort', $manageableIds ?: [-1])
            ->when($filterCategory, fn ($q) => $q->where('content_sort', $filterCategory))
            ->with(['sort:id,name', 'submitter:member_id,name'])
            ->orderBy('adddatetime')->orderBy('id')
            ->paginate(20);

        return view('member.content-review', [
            'items' => $items,
            'filterCategory' => $filterCategory ? ContentSort::find($filterCategory) : null,
        ]);
    }

    public function approve(Request $request, Content $content): RedirectResponse
    {
        $this->assertCanReview($request, $content);

        $content->ok = 1;
        $content->oktime = now();
        $content->review_note = null;
        $content->save();

        return back()->with('status', '投稿を承認して公開しました。');
    }

    public function reject(Request $request, Content $content): RedirectResponse
    {
        $this->assertCanReview($request, $content);

        $data = $request->validate([
            'note' => ['required', 'string', 'max:1000'],
        ], [], ['note' => '却下理由']);

        $content->ok = 0; // 下書きに差し戻し
        $content->review_note = $data['note'];
        $content->save();

        return back()->with('status', '投稿を差し戻しました。');
    }

    /** サブカテゴリ追加フォーム（カテゴリ管理員のみ）。 */
    public function subcategoryForm(Request $request, int $category): View
    {
        $member = $this->member();
        $parent = $this->visibleCategory($category);
        abort_unless($member->managesCategory($parent), 403);

        return view('member.content-subcategory', ['parent' => $parent]);
    }

    public function subcategory(Request $request, int $category): RedirectResponse
    {
        $member = $this->member();
        $parent = $this->visibleCategory($category);
        abort_unless($member->managesCategory($parent), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'introduce' => ['nullable', 'string', 'max:100000'],
        ], [], ['name' => 'カテゴリ名', 'introduce' => '説明']);

        $cat = new ContentSort;
        $cat->name = $data['name'];
        $cat->father_id = $parent->id;
        $cat->introduce = RichText::clean($data['introduce'] ?? null);
        $cat->junban = ((int) ContentSort::query()->where('father_id', $parent->id)->max('junban')) + 1;
        $cat->save(); // BelongsToSite が site_id をセット

        return redirect()->route('contents.index', ['category' => $cat->id])
            ->with('status', 'サブカテゴリを追加しました。');
    }

    /** 「カテゴリ管理員になる」申請。 */
    public function applyManager(Request $request, int $category): RedirectResponse
    {
        $member = $this->member();
        $cat = $this->visibleCategory($category);

        if ($member->managesCategory($cat)) {
            return back()->with('status', 'すでにこのカテゴリの管理員です。');
        }

        $existing = ContentSortManager::query()
            ->where('content_sort_id', $cat->id)
            ->where('member_id', $member->getKey())
            ->first();

        if ($existing) {
            return back()->with('status', 'このカテゴリの管理員申請はすでに送信済みです（承認待ち）。');
        }

        ContentSortManager::query()->create([
            'content_sort_id' => $cat->id,
            'member_id' => $member->getKey(),
            'site_id' => (string) $cat->site_id,
            'status' => 'pending',
            'applied_at' => now(),
        ]);

        return back()->with('status', "「{$cat->name}」のカテゴリ管理員に申請しました。スーパー管理員の承認をお待ちください。");
    }

    private function member(): Member
    {
        $member = request()->user();
        abort_unless($member instanceof Member && $member->belongsToSite(), 403);

        return $member;
    }

    /** 現在サイトの公開カテゴリ。無ければ 404。 */
    private function visibleCategory(int $id): ContentSort
    {
        $cat = ContentSort::query()->publicVisible()->find($id);
        if (! $cat) {
            throw new NotFoundHttpException;
        }

        return $cat;
    }

    private function assertCanReview(Request $request, Content $content): void
    {
        // 他サイトの content は BelongsToSite の暗黙バインドで既に 404。
        abort_unless($this->member()->managesCategory((int) $content->content_sort), 403);
    }
}
