<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\MemberRoom;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 旧ASPの「管理員メニュー」（ページ上の編集/非表示ボタン群）は Filament 管理画面に
 * 一本化した。フロント側からの導線として、サイト管理員には「管理画面」リンクを
 * 表示する（一般の参加者・ゲストには出さない）。
 */
class AdminEntryLinkTest extends TestCase
{
    use RefreshDatabase;

    private function site(): Room
    {
        return Room::create(['site_id' => 'www', 'sitename' => 'テスト', 'site_joutai' => 1, 'function_list' => '']);
    }

    public function test_site_manager_sees_admin_link_on_public_and_member_pages(): void
    {
        $this->site();
        $manager = Member::create(['member_id' => 'm1', 'name' => 'テスト']);
        MemberRoom::create(['member_id' => 'm1', 'site_id' => 'www', 'ninshou' => -1]);

        $this->actingAs($manager)->get('/')->assertOk()->assertSee('href="/admin"', false);
        $this->actingAs($manager)->get('/mypage')->assertOk()->assertSee('href="/admin"', false);
    }

    public function test_plain_project_member_does_not_see_admin_link(): void
    {
        $this->site();
        $member = Member::create(['member_id' => 'm1', 'name' => 'テスト']);
        MemberRoom::create(['member_id' => 'm1', 'site_id' => 'www', 'ninshou' => 1]);

        $this->actingAs($member)->get('/')->assertOk()->assertDontSee('href="/admin"', false);
        $this->actingAs($member)->get('/mypage')->assertOk()->assertDontSee('href="/admin"', false);
    }

    public function test_guest_does_not_see_admin_link(): void
    {
        $this->site();

        $this->get('/')->assertOk()->assertDontSee('href="/admin"', false);
    }
}
