<?php

namespace Tests\Feature;

use App\Models\Guestbook;
use App\Models\GuestbookCategory;
use App\Models\Member;
use App\Models\MemberRoom;
use App\Models\MessageItem;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 掲示板の投稿・返信、メッセージ本文はリッチテキスト（Trix）。
 * 保存時に App\Support\RichText でサニタイズする。
 */
class BoardMessageRichTextTest extends TestCase
{
    use RefreshDatabase;

    private function participant(string $functions): Member
    {
        Room::create(['site_id' => 'www', 'sitename' => 'テスト', 'site_joutai' => 1, 'function_list' => $functions]);
        $member = Member::create(['member_id' => 'p1', 'name' => '参加者太郎']);
        MemberRoom::create(['member_id' => 'p1', 'site_id' => 'www', 'ninshou' => 1]);

        return $member;
    }

    private function siteBoard(): GuestbookCategory
    {
        return GuestbookCategory::withoutGlobalScope('site')->create([
            'id' => GuestbookCategory::SITE_BOARD_ID, 'site_id' => 'www', 'name' => 'サイト掲示板',
        ]);
    }

    public function test_board_thread_stores_sanitized_html(): void
    {
        $member = $this->participant('freeguestbookfunction');
        $cat = $this->siteBoard();

        $this->actingAs($member)->post(route('board.store', $cat->id), [
            'title' => 'テストスレッド',
            'content' => '<div><strong>太字</strong></div><script>alert(1)</script>'
                .'<a href="javascript:evil()">bad</a>',
        ])->assertRedirect();

        $post = Guestbook::withoutGlobalScope('site')->where('title', 'テストスレッド')->firstOrFail();
        $this->assertStringContainsString('<strong>太字</strong>', $post->content);
        $this->assertStringNotContainsString('<script', $post->content);
        $this->assertStringNotContainsString('javascript:', $post->content);
    }

    public function test_board_reply_stores_sanitized_html(): void
    {
        $member = $this->participant('freeguestbookfunction');
        $cat = $this->siteBoard();
        $thread = Guestbook::withoutGlobalScope('site')->create([
            'site_id' => 'www', 'category' => $cat->id, 'title' => '親', 'parent' => '0', 'top' => '0',
            'space_num' => 0, 'user_name' => 'p1', 'create_date' => now(),
        ]);

        $this->actingAs($member)->post(route('board.reply', $thread->id), [
            'title' => '返信',
            'content' => '<div>返信本文</div><iframe src="evil"></iframe>',
        ])->assertRedirect();

        $reply = Guestbook::withoutGlobalScope('site')->where('parent', (string) $thread->id)->firstOrFail();
        $this->assertStringContainsString('返信本文', $reply->content);
        $this->assertStringNotContainsString('<iframe', $reply->content);
    }

    public function test_message_stores_sanitized_html_and_rejects_empty(): void
    {
        $member = $this->participant('dengonfunction');
        MemberRoom::create(['member_id' => 'to1', 'site_id' => 'www', 'ninshou' => 1]);
        Member::create(['member_id' => 'to1', 'name' => '宛先花子']);

        // 空（Trix の空エディタ）は弾く
        $this->actingAs($member)->post(route('messages.store'), [
            'to' => 'to1', 'content' => '<div><br></div>',
        ])->assertSessionHasErrors('content');

        // 正常
        $this->actingAs($member)->post(route('messages.store'), [
            'to' => 'to1',
            'content' => '<div>本文<strong>強調</strong></div><script>x</script>',
        ])->assertRedirect(route('messages.sent'));

        $msg = MessageItem::withoutGlobalScope('site')->where('to', 'to1')->firstOrFail();
        $this->assertStringContainsString('<strong>強調</strong>', $msg->content);
        $this->assertStringNotContainsString('<script', $msg->content);
    }

    public function test_message_recipient_must_be_a_site_participant(): void
    {
        $member = $this->participant('dengonfunction');

        $this->actingAs($member)->post(route('messages.store'), [
            'to' => 'stranger', 'content' => '<div>hi</div>',
        ])->assertSessionHasErrors('to');
    }
}
