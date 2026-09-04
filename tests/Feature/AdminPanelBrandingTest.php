<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\MemberRoom;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Filament管理画面のブランディング（ロゴ・ホームURL・ナビゲーション日本語化・
 * MyPageへ戻る導線）を確認する。
 */
class AdminPanelBrandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_shows_japanese_navigation_and_no_filament_info_widget(): void
    {
        Room::create([
            'site_id' => 'www', 'sitename' => 'テスト', 'site_joutai' => 1,
            'function_list' => '', 'logo' => 'img/logoCTO.png',
        ]);
        $manager = Member::create(['member_id' => 'm1', 'name' => 'テスト']);
        MemberRoom::create(['member_id' => 'm1', 'site_id' => 'www', 'ninshou' => -1]);

        $response = $this->actingAs($manager)->get('/admin')->assertOk();

        // 日本語化されたナビゲーションラベル
        $response->assertSee('トップメニュー');
        $response->assertSee('コンテンツカテゴリ');

        // filament の宣伝ウィジェット（バージョン番号・ドキュメント/GitHubへのリンク）が外れている
        $response->assertDontSee('fi-filament-info-widget', false);

        // ロゴ画像（rooms.logo）を使っている
        $response->assertSee('img/logoCTO.png', false);

        // マイページへ戻る導線
        $response->assertSee('マイページへ戻る');
    }
}
