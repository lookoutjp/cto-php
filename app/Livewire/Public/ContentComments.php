<?php

namespace App\Livewire\Public;

use App\Models\Content;
use App\Models\ContentComment;
use App\Models\Member;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * 公開コンテンツ詳細に埋め込むユーザーコメント欄。
 * 旧ASP: ContentCommentSon.asp / ContentComment_Write.asp / ContentCommentList.asp。
 *
 *   <livewire:public.content-comments :content="$content" />
 *
 * 表示: `commentfunction` かつ content.commentok=1 のときのみ（誰でも閲覧可）。
 * 投稿: 現在サイトのプロジェクト参加者（ninshou 1/-1）のみ。
 */
class ContentComments extends Component
{
    use WithPagination;

    public int $contentId;

    public string $contentName = '';

    public string $body = '';

    public function mount(Content $content): void
    {
        $this->contentId = (int) $content->id;
        $this->contentName = (string) $content->name;
    }

    #[Computed]
    public function canPost(): bool
    {
        $user = auth()->user();

        return $user instanceof Member && $user->isProjectMemberOf();
    }

    public function submit(): void
    {
        abort_unless($this->canPost(), 403);

        $data = $this->validate([
            'body' => ['required', 'string', 'max:2000'],
        ], [], ['body' => 'コメント']);

        $comment = new ContentComment;
        $comment->comment = $data['body'];
        $comment->content_id = (string) $this->contentId;
        $comment->member_id = auth()->user()->getKey();
        $comment->name = $this->contentName;
        $comment->ninshou = 1;
        $comment->time = now()->format('Y/m/d H:i:s');
        $comment->save(); // BelongsToSite が site_id をセット

        $this->reset('body');
        $this->resetPage();
        session()->flash('comment-status', 'コメントを投稿しました。');
    }

    public function render(): View
    {
        $comments = ContentComment::query()
            ->forContent($this->contentId)
            ->newestFirst()
            ->with('member:member_id,name')
            ->paginate(10);

        return view('livewire.public.content-comments', compact('comments'));
    }
}
