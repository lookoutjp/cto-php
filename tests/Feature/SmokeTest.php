<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\MemberRoom;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * デプロイ前の最低限の疎通。DB は空（RefreshDatabase）でも通る範囲。
 */
class SmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoint(): void
    {
        $this->get('/up')->assertOk();
    }

    public function test_login_page_renders(): void
    {
        $this->get('/login')->assertOk()->assertSee('ログイン');
    }

    public function test_admin_login_page_renders(): void
    {
        $this->get('/admin/login')->assertOk();
    }

    public function test_public_home_renders_for_a_site(): void
    {
        Room::create([
            'site_id' => 'www',
            'sitename' => 'テストサイト',
            'site_joutai' => 1,
            'function_list' => '',
        ]);

        $this->get('/')->assertOk();
    }

    public function test_legal_pages_render(): void
    {
        Room::create([
            'site_id' => 'www',
            'sitename' => 'テストサイト',
            'site_joutai' => 1,
            'function_list' => '',
        ]);

        $this->get('/legal/tokushoho')->assertOk()->assertSee('特定商取引法に基づく表記');
        $this->get('/legal/terms')->assertOk()->assertSee('利用規約');
        $this->get('/legal/privacy')->assertOk()->assertSee('プライバシーポリシー');
    }

    /**
     * 公開フロントの「マイページ」リンクは route('dashboard')（実体は /mypage）を指す。
     * url('/dashboard') のような決め打ちに戻すと 404 になる（dashboard は route 名であって
     * URL パスではない）。
     */
    public function test_mypage_link_points_to_the_real_mypage_route(): void
    {
        Room::create([
            'site_id' => 'www', 'sitename' => 'テストサイト', 'site_joutai' => 1, 'function_list' => '',
        ]);
        $member = Member::create(['member_id' => 'm1', 'name' => 'テスト']);
        MemberRoom::create(['member_id' => 'm1', 'site_id' => 'www', 'ninshou' => 0]);

        $response = $this->actingAs($member)->get('/')->assertOk();

        $response->assertSee('href="'.route('dashboard').'"', false);
        $this->get(route('dashboard'))->assertOk(); // リンク先が実在し、既にログイン中なので開ける
    }
}
