<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\MemberRoom;
use App\Models\MessageItem;
use App\Models\Room;
use App\Support\CurrentSite;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * 社内メッセージ（伝言）。旧ASP: Member_MessageSend.asp ほか。
 * dengonfunction が有効なサイトのプロジェクト参加者が利用できる。
 */
class MessageController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureEnabled();
        $me = $request->user()->getKey();

        return view('member.message-index', [
            'box' => 'inbox',
            'messages' => MessageItem::query()->inbox($me)->with('sender:member_id,name')->paginate(20),
        ]);
    }

    public function sent(Request $request): View
    {
        $this->ensureEnabled();
        $me = $request->user()->getKey();

        return view('member.message-index', [
            'box' => 'sent',
            'messages' => MessageItem::query()->outbox($me)->with('recipient:member_id,name')->paginate(20),
        ]);
    }

    public function show(Request $request, int $id): View
    {
        $this->ensureEnabled();
        $me = (string) $request->user()->getKey();

        $message = MessageItem::query()->with('sender:member_id,name', 'recipient:member_id,name')->findOrFail($id);
        abort_unless((string) $message->from === $me || (string) $message->to === $me, 403);

        if ((string) $message->to === $me && ! $message->readed) {
            $message->readed = true;
            $message->save();
        }

        return view('member.message-show', compact('message'));
    }

    public function create(): View
    {
        $this->ensureEnabled();

        return view('member.message-form', ['members' => $this->siteMembers()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureEnabled();

        $memberIds = $this->siteMembers()->pluck('member_id')->all();

        $data = $request->validate([
            'to' => ['required', 'string', 'in:'.implode(',', $memberIds)],
            'content' => ['required', 'string', 'max:20000'],
        ], [], ['to' => '宛先', 'content' => '本文']);

        $m = new MessageItem;
        $m->from = $request->user()->getKey();
        $m->to = $data['to'];
        $m->content = $data['content'];
        $m->time = now();
        $m->readed = false;
        $m->delete_from = false;
        $m->delete_to = false;
        $m->save(); // BelongsToSite が site_id をセット

        return redirect()->route('messages.sent')->with('status', 'メッセージを送信しました。');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $this->ensureEnabled();
        $me = (string) $request->user()->getKey();

        $message = MessageItem::query()->findOrFail($id);

        if ((string) $message->to === $me) {
            $message->delete_to = true;
        } elseif ((string) $message->from === $me) {
            $message->delete_from = true;
        } else {
            abort(403);
        }
        $message->save();

        return redirect()->route(
            (string) $message->from === $me ? 'messages.sent' : 'messages.index'
        )->with('status', 'メッセージを削除しました。');
    }

    private function siteMembers()
    {
        $ids = MemberRoom::query()
            ->where('site_id', app(CurrentSite::class)->id())
            ->whereIn('ninshou', [1, -1])
            ->pluck('member_id');

        return Member::query()
            ->whereIn('member_id', $ids)
            ->whereKeyNot(request()->user()->getKey())
            ->orderBy('name')
            ->get(['member_id', 'name']);
    }

    private function ensureEnabled(): void
    {
        if (! Room::find(app(CurrentSite::class)->id())?->hasFunction('dengonfunction')) {
            throw new NotFoundHttpException;
        }
    }
}
