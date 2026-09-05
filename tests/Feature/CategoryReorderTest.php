<?php

namespace Tests\Feature;

use App\Models\ContentSort;
use App\Models\Member;
use App\Models\MemberRoom;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 公開ページ左サイドバー「カテゴリ」の管理者モードでのドラッグ&ドロップ並び替え。
 */
class CategoryReorderTest extends TestCase
{
    use RefreshDatabase;

    private function site(): Room
    {
        return Room::create(['site_id' => 'www', 'sitename' => 'テスト', 'site_joutai' => 1, 'function_list' => '']);
    }

    public function test_manager_can_reorder_top_level_categories(): void
    {
        $this->site();
        $a = ContentSort::create(['site_id' => 'www', 'name' => 'A', 'father_id' => 0, 'junban' => 1]);
        $b = ContentSort::create(['site_id' => 'www', 'name' => 'B', 'father_id' => 0, 'junban' => 2]);
        $c = ContentSort::create(['site_id' => 'www', 'name' => 'C', 'father_id' => 0, 'junban' => 3]);

        $manager = Member::create(['member_id' => 'm1', 'name' => 'テスト']);
        MemberRoom::create(['member_id' => 'm1', 'site_id' => 'www', 'ninshou' => -1]);

        $this->actingAs($manager)
            ->postJson(route('categories.reorder'), ['ids' => [$c->id, $a->id, $b->id]])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertSame(1, $c->fresh()->junban);
        $this->assertSame(2, $a->fresh()->junban);
        $this->assertSame(3, $b->fresh()->junban);
    }

    public function test_plain_member_cannot_reorder_categories(): void
    {
        $this->site();
        $a = ContentSort::create(['site_id' => 'www', 'name' => 'A', 'father_id' => 0, 'junban' => 1]);
        $b = ContentSort::create(['site_id' => 'www', 'name' => 'B', 'father_id' => 0, 'junban' => 2]);

        $member = Member::create(['member_id' => 'm1', 'name' => 'テスト']);
        MemberRoom::create(['member_id' => 'm1', 'site_id' => 'www', 'ninshou' => 1]);

        $this->actingAs($member)
            ->postJson(route('categories.reorder'), ['ids' => [$b->id, $a->id]])
            ->assertForbidden();

        $this->assertSame(1, $a->fresh()->junban);
        $this->assertSame(2, $b->fresh()->junban);
    }

    public function test_guest_cannot_reorder_categories(): void
    {
        $this->site();

        $this->postJson(route('categories.reorder'), ['ids' => [1, 2]])->assertUnauthorized();
    }

    /**
     * 子カテゴリ（トップレベルでない）のIDを紛れ込ませても無視される
     * （father_id を書き換える経路にはならず、junban も更新されない）。
     */
    public function test_child_category_ids_are_ignored(): void
    {
        $this->site();
        $root1 = ContentSort::create(['site_id' => 'www', 'name' => 'ルート1', 'father_id' => 0, 'junban' => 1]);
        $root2 = ContentSort::create(['site_id' => 'www', 'name' => 'ルート2', 'father_id' => 0, 'junban' => 2]);
        $child = ContentSort::create(['site_id' => 'www', 'name' => '子', 'father_id' => $root1->id, 'junban' => 5]);

        $manager = Member::create(['member_id' => 'm1', 'name' => 'テスト']);
        MemberRoom::create(['member_id' => 'm1', 'site_id' => 'www', 'ninshou' => -1]);

        $this->actingAs($manager)
            ->postJson(route('categories.reorder'), ['ids' => [$child->id, $root2->id, $root1->id]])
            ->assertOk();

        // 子カテゴリはトップレベルでないため無視され、junban=5 のまま・father_id も変わらない
        $this->assertSame(5, $child->fresh()->junban);
        $this->assertSame((string) $root1->id, (string) $child->fresh()->father_id);
        // トップレベルの2件だけが、紛れ込んだ子IDを除いた順で並び替えられる
        $this->assertSame(1, $root2->fresh()->junban);
        $this->assertSame(2, $root1->fresh()->junban);
    }
}
