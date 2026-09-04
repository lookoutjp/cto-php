<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\MemberRoom;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * マイページの右サイドバー「MyMenu」（旧ASP相当、新機能の集約メニュー）。
 */
class MyMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_member_sees_function_gated_sections(): void
    {
        Room::create([
            'site_id' => 'www', 'sitename' => 'テスト', 'site_joutai' => 1,
            'function_list' => 'todofunction,wbsfunction,dengonfunction',
        ]);
        $member = Member::create(['member_id' => 'm1', 'name' => 'テスト']);
        MemberRoom::create(['member_id' => 'm1', 'site_id' => 'www', 'ninshou' => 1]);

        $response = $this->actingAs($member)->get('/mypage')->assertOk();

        $response->assertSee('MyMenu');
        $response->assertSee('TODO管理');
        $response->assertSee('WBS管理');
        $response->assertSee('メッセージ');
        // 有効化していない機能のセクションは出ない
        $response->assertDontSee('課題管理');
        $response->assertDontSee('サーベイ');
    }

    public function test_content_viewer_only_member_does_not_see_business_sections(): void
    {
        Room::create([
            'site_id' => 'www', 'sitename' => 'テスト', 'site_joutai' => 1,
            'function_list' => 'todofunction',
        ]);
        $member = Member::create(['member_id' => 'm1', 'name' => 'テスト']);
        MemberRoom::create(['member_id' => 'm1', 'site_id' => 'www', 'ninshou' => 0]);

        // ninshou=0 は mypage-lite になるため MyMenu 自体が出ない
        $response = $this->actingAs($member)->get('/mypage')->assertOk();

        $response->assertDontSee('MyMenu');
    }
}
