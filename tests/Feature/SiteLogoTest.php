<?php

namespace Tests\Feature;

use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * ヘッダー・ログイン画面のロゴが rooms.logo（旧ASPの実ロゴ画像）を優先して
 * 表示することを確認する。未設定ならデフォルトのSVGにフォールバックする。
 */
class SiteLogoTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_header_uses_the_configured_room_logo(): void
    {
        Room::create([
            'site_id' => 'www', 'sitename' => 'テスト', 'site_joutai' => 1,
            'function_list' => '', 'logo' => 'img/logoCTO.png',
        ]);

        $this->get('/')->assertOk()->assertSee('img/logoCTO.png', false);
    }

    public function test_login_page_uses_the_configured_room_logo(): void
    {
        Room::create([
            'site_id' => 'www', 'sitename' => 'テスト', 'site_joutai' => 1,
            'function_list' => '', 'logo' => 'img/logoCTO.png',
        ]);

        $this->get('/login')->assertOk()->assertSee('img/logoCTO.png', false);
    }

    public function test_falls_back_to_default_logo_when_room_has_none(): void
    {
        Room::create([
            'site_id' => 'www', 'sitename' => 'テスト', 'site_joutai' => 1, 'function_list' => '',
        ]);

        $response = $this->get('/')->assertOk();
        $response->assertDontSee('<img', false);
    }
}
