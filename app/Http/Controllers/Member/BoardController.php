<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Guestbook;
use App\Models\GuestbookCategory;
use App\Models\Room;
use App\Support\CurrentSite;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * 掲示板（コミュニティ）。旧ASP: meetlist.asp / meet.asp / meet_disp.asp / meetadd.asp / meet_re.asp。
 *
 * - カテゴリ id=1 は「サイト掲示板」（全参加者向け既定カテゴリ）
 * - スレッドは guestbooks の自己参照ツリー（parent / top / space_num）
 * - 管理員返信（revert）は Filament 側で編集
 *
 * 利用条件は EnsureProjectMember（ninshou 1/-1）＋ freeguestbookfunction。
 */
class BoardController extends Controller
{
    public function index(): View
    {
        $this->ensureEnabled();

        $siteBoard = GuestbookCategory::query()->find(GuestbookCategory::SITE_BOARD_ID);
        $communities = GuestbookCategory::query()->communities()->get();

        $counts = Guestbook::query()->real()->threads()
            ->selectRaw('category, count(*) as c')
            ->groupBy('category')
            ->pluck('c', 'category');

        return view('member.board-index', compact('siteBoard', 'communities', 'counts'));
    }

    public function category(Request $request, int $category): View
    {
        $this->ensureEnabled();
        $cat = $this->findCategory($category);

        $threads = Guestbook::query()->real()->threads()
            ->inCategory($cat->id)
            ->with('author:member_id,name')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        // 返信数をまとめて取得（top = スレッドID）
        $replyCounts = Guestbook::query()->real()
            ->whereIn('top', $threads->pluck('id')->map(fn ($id) => (string) $id))
            ->selectRaw('top, count(*) as c')
            ->groupBy('top')
            ->pluck('c', 'top');

        return view('member.board-category', compact('cat', 'threads', 'replyCounts'));
    }

    public function show(int $thread): View
    {
        $this->ensureEnabled();

        $post = Guestbook::query()->real()->threads()->with('author:member_id,name')->find($thread);
        if (! $post) {
            throw new NotFoundHttpException;
        }

        $cat = $this->findCategory((int) $post->category);
        $replies = $post->replyTree();

        return view('member.board-thread', compact('post', 'cat', 'replies'));
    }

    public function create(int $category): View
    {
        $this->ensureEnabled();
        $cat = $this->findCategory($category);

        return view('member.board-form', ['cat' => $cat, 'parent' => null]);
    }

    public function store(Request $request, int $category): RedirectResponse
    {
        $this->ensureEnabled();
        $cat = $this->findCategory($category);

        $data = $this->validated($request);

        $post = new Guestbook;
        $post->fill($data);
        $post->category = $cat->id;
        $post->parent = '0';
        $post->top = '0';
        $post->space_num = 0;
        $post->orders = 0;
        $post->user_name = $request->user()->getKey();
        $post->create_date = now();
        $post->save(); // BelongsToSite が site_id をセット

        return redirect()->route('board.show', $post->id)
            ->with('status', 'スレッドを投稿しました。');
    }

    public function reply(Request $request, int $thread): RedirectResponse
    {
        $this->ensureEnabled();

        $parent = Guestbook::query()->real()->find($thread);
        if (! $parent) {
            throw new NotFoundHttpException;
        }

        $data = $this->validated($request);

        $rootId = in_array((string) ($parent->parent ?? '0'), ['0', ''], true)
            ? (string) $parent->id
            : (string) $parent->top;

        $post = new Guestbook;
        $post->fill($data);
        $post->category = (int) $parent->category;
        $post->parent = (string) $parent->id;
        $post->top = $rootId;
        $post->space_num = (int) $parent->space_num + 1;
        $post->orders = 0;
        $post->user_name = $request->user()->getKey();
        $post->create_date = now();
        $post->save();

        return redirect()->route('board.show', $rootId)
            ->with('status', '返信を投稿しました。');
    }

    /** @return array{title: string, content: ?string} */
    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string', 'max:20000'],
        ], [], ['title' => 'タイトル', 'content' => '本文']);
    }

    private function findCategory(int $id): GuestbookCategory
    {
        $cat = GuestbookCategory::query()->find($id);
        if (! $cat) {
            throw new NotFoundHttpException;
        }

        return $cat;
    }

    private function ensureEnabled(): void
    {
        if (! Room::find(app(CurrentSite::class)->id())?->hasFunction('freeguestbookfunction')) {
            throw new NotFoundHttpException;
        }
    }
}
