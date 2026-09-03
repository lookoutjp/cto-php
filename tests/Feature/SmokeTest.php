<?php

namespace Tests\Feature;

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
}
