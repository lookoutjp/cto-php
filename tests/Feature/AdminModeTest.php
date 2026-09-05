<?php

namespace Tests\Feature;

use App\Models\ContentSort;
use App\Models\Member;
use App\Models\MemberRoom;
use App\Models\Room;
use App\Models\TopMenu;
use App\Support\AdminMode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 旧ASPの「管理員モード」相当。サイト管理員がONにしている間だけ、公開ページの
 * トップメニュー・カテゴリに追加/編集アイコンが出る（フェーズ1: この2つのみ）。
 */
class AdminModeTest extends TestCase
{
    use RefreshDatabase;

    private function site(): Room
    {
        return Room::create(['site_id' => 'www', 'sitename' => 'テスト', 'site_joutai' => 1, 'function_list' => '']);
    }

    public function test_manager_can_toggle_admin_mode_and_sees_inline_controls(): void
    {
        $this->site();
        TopMenu::create(['site_id' => 'www', 'menuname' => 'サイト概要', 'linkaddress' => '/about', 'junban' => 1]);
        ContentSort::create(['site_id' => 'www', 'name' => 'よくある質問', 'junban' => 1]);

        $manager = Member::create(['member_id' => 'm1', 'name' => 'テスト']);
        MemberRoom::create(['member_id' => 'm1', 'site_id' => 'www', 'ninshou' => -1]);

        // OFFの間は編集アイコン・追加ボタンは出ない
        $this->actingAs($manager)->get('/')->assertOk()
            ->assertDontSee('メニューを追加')
            ->assertDontSee('カテゴリを追加');

        $this->actingAs($manager)->post(route('admin-mode.toggle'))->assertRedirect();
        $this->assertTrue(AdminMode::isEnabled('www'));

        $response = $this->actingAs($manager)->get('/')->assertOk();
        $response->assertSee('管理者モードで表示中');
        $response->assertSee('＋ メニューを追加', false);
        $response->assertSee('＋ カテゴリを追加', false);
        $response->assertSee(route('filament.admin.resources.top-menus.create'), false);
        $response->assertSee(route('filament.admin.resources.content-sorts.create'), false);

        // 再度トグルでOFFに戻る
        $this->actingAs($manager)->post(route('admin-mode.toggle'));
        $this->assertFalse(AdminMode::isEnabled('www'));
    }

    public function test_plain_member_cannot_toggle_admin_mode(): void
    {
        $this->site();
        $member = Member::create(['member_id' => 'm1', 'name' => 'テスト']);
        MemberRoom::create(['member_id' => 'm1', 'site_id' => 'www', 'ninshou' => 1]);

        $this->actingAs($member)->post(route('admin-mode.toggle'))->assertForbidden();
        $this->assertFalse(AdminMode::isEnabled('www'));
    }

    public function test_guest_cannot_toggle_admin_mode(): void
    {
        $this->site();

        $this->post(route('admin-mode.toggle'))->assertRedirect(route('login'));
    }

    public function test_mypage_shows_admin_menu_panel_only_for_managers(): void
    {
        $this->site();
        $manager = Member::create(['member_id' => 'm1', 'name' => 'テスト']);
        MemberRoom::create(['member_id' => 'm1', 'site_id' => 'www', 'ninshou' => -1]);

        $this->actingAs($manager)->get('/mypage')->assertOk()
            ->assertSee('管理者メニュー')
            ->assertSee('トップメニュー管理')
            ->assertSee('カテゴリ管理');

        $member = Member::create(['member_id' => 'm2', 'name' => '一般']);
        MemberRoom::create(['member_id' => 'm2', 'site_id' => 'www', 'ninshou' => 1]);

        $this->actingAs($member)->get('/mypage')->assertOk()
            ->assertDontSee('管理者メニュー')
            ->assertDontSee('トップメニュー管理');
    }
}
