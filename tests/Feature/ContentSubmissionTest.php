<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\ContentSort;
use App\Models\ContentSortManager;
use App\Models\Member;
use App\Models\MemberRoom;
use App\Models\Room;
use App\Support\CurrentSite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 会員のコンテンツ投稿（審査待ち → カテゴリ管理員の承認で公開）。
 */
class ContentSubmissionTest extends TestCase
{
    use RefreshDatabase;

    private ContentSort $parent;

    private ContentSort $child;

    protected function setUp(): void
    {
        parent::setUp();
        Room::create(['site_id' => 'www', 'sitename' => 'テスト', 'site_joutai' => 1, 'function_list' => '']);
        app(CurrentSite::class)->set('www');

        $this->parent = ContentSort::create(['site_id' => 'www', 'name' => '親カテゴリ', 'father_id' => 0, 'junban' => 1]);
        $this->child = ContentSort::create(['site_id' => 'www', 'name' => '子カテゴリ', 'father_id' => $this->parent->id, 'junban' => 1]);
    }

    private function member(string $id, int $ninshou = 0): Member
    {
        $m = Member::create(['member_id' => $id, 'name' => strtoupper($id)]);
        MemberRoom::create(['member_id' => $id, 'site_id' => 'www', 'ninshou' => $ninshou]);

        return $m;
    }

    public function test_plain_member_submission_is_pending_and_hidden_from_public(): void
    {
        $member = $this->member('taro');

        $this->actingAs($member)->post(route('contents.submit', $this->child->id), [
            'name' => 'みんなに見せたい記事',
            'explain' => '<div>本文です</div>',
        ])->assertRedirect(route('contents.mine'));

        $content = Content::withoutGlobalScope('site')->where('name', 'みんなに見せたい記事')->firstOrFail();
        $this->assertSame(2, (int) $content->ok);
        $this->assertSame('taro', $content->member_id);

        // 公開ページ（ツリー）に出ない
        $this->get(route('contents.index'))->assertOk()->assertDontSee('みんなに見せたい記事');
        // 詳細も 404
        $this->get(route('contents.show', $content))->assertNotFound();
    }

    public function test_category_manager_of_ancestor_can_approve_descendant_submission(): void
    {
        $author = $this->member('taro');
        $manager = $this->member('mgr');
        ContentSortManager::create([
            'content_sort_id' => $this->parent->id, 'member_id' => 'mgr',
            'site_id' => 'www', 'status' => 'approved', 'decided_at' => now(),
        ]);

        $this->actingAs($author)->post(route('contents.submit', $this->child->id), [
            'name' => '承認される記事', 'explain' => '<div>x</div>',
        ]);
        $content = Content::withoutGlobalScope('site')->where('name', '承認される記事')->firstOrFail();

        $this->actingAs($manager)->post(route('contents.approve', $content->id))->assertRedirect();

        $this->assertSame(1, (int) $content->fresh()->ok);
        $this->get(route('contents.index'))->assertOk()->assertSee('承認される記事');
    }

    public function test_non_manager_cannot_approve(): void
    {
        $author = $this->member('taro');
        $other = $this->member('other');

        $this->actingAs($author)->post(route('contents.submit', $this->child->id), [
            'name' => 'x', 'explain' => '<div>x</div>',
        ]);
        $content = Content::withoutGlobalScope('site')->where('name', 'x')->firstOrFail();

        $this->actingAs($other)->post(route('contents.approve', $content->id))->assertForbidden();
    }

    public function test_reject_sends_back_to_draft_with_note_and_author_can_resubmit(): void
    {
        $author = $this->member('taro');
        $manager = $this->member('mgr');
        ContentSortManager::create([
            'content_sort_id' => $this->child->id, 'member_id' => 'mgr',
            'site_id' => 'www', 'status' => 'approved', 'decided_at' => now(),
        ]);

        $this->actingAs($author)->post(route('contents.submit', $this->child->id), [
            'name' => '差し戻される記事', 'explain' => '<div>x</div>',
        ]);
        $content = Content::withoutGlobalScope('site')->where('name', '差し戻される記事')->firstOrFail();

        $this->actingAs($manager)->post(route('contents.reject', $content->id), ['note' => '出典を明記してください'])
            ->assertRedirect();

        $content->refresh();
        $this->assertSame(0, (int) $content->ok);
        $this->assertSame('出典を明記してください', $content->review_note);

        // 投稿者が編集して再申請 → ok=2 に戻り review_note がクリアされる
        $this->actingAs($author)->post(route('contents.submit', $this->child->id), [
            'edit' => $content->id,
            'name' => '差し戻される記事（改）', 'explain' => '<div>出典: ...</div>',
        ])->assertRedirect(route('contents.mine'));

        $content->refresh();
        $this->assertSame(2, (int) $content->ok);
        $this->assertNull($content->review_note);
        $this->assertSame('差し戻される記事（改）', $content->name);
    }

    public function test_category_manager_submission_publishes_directly(): void
    {
        $manager = $this->member('mgr');
        ContentSortManager::create([
            'content_sort_id' => $this->child->id, 'member_id' => 'mgr',
            'site_id' => 'www', 'status' => 'approved', 'decided_at' => now(),
        ]);

        $this->actingAs($manager)->post(route('contents.submit', $this->child->id), [
            'name' => '管理員の直接投稿', 'explain' => '<div>x</div>',
        ])->assertRedirect();

        $content = Content::withoutGlobalScope('site')->where('name', '管理員の直接投稿')->firstOrFail();
        $this->assertSame(1, (int) $content->ok);
    }

    public function test_category_manager_can_add_subcategory(): void
    {
        $manager = $this->member('mgr');
        ContentSortManager::create([
            'content_sort_id' => $this->parent->id, 'member_id' => 'mgr',
            'site_id' => 'www', 'status' => 'approved', 'decided_at' => now(),
        ]);

        $this->actingAs($manager)->post(route('contents.subcategory', $this->child->id), [
            'name' => '孫カテゴリ',
        ])->assertRedirect();

        $this->assertDatabaseHas('content_sorts', [
            'name' => '孫カテゴリ', 'father_id' => $this->child->id, 'site_id' => 'www',
        ]);
    }

    public function test_non_site_member_cannot_submit(): void
    {
        $stranger = Member::create(['member_id' => 'stranger', 'name' => 'X']); // no MemberRoom

        $this->actingAs($stranger)->post(route('contents.submit', $this->child->id), [
            'name' => 'x', 'explain' => '<div>x</div>',
        ])->assertForbidden();
    }
}
