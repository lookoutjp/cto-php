<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\TopMenu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * サイト概要（旧 aboutsite.asp）。/about として新設。
 */
class AboutPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_about_page_shows_site_intro_and_operator_info(): void
    {
        Room::create([
            'site_id' => 'www',
            'sitename' => 'テストサイト',
            'site_joutai' => 1,
            'function_list' => '',
            'siteintro' => '<p>これはテストサイトの紹介文です。</p>',
            'comname' => 'テスト株式会社',
            'comphone' => '03-1234-5678',
        ]);

        $response = $this->get('/about')->assertOk();

        $response->assertSee('サイト概要', false);
        $response->assertSee('これはテストサイトの紹介文です。', false);
        $response->assertSee('テスト株式会社');
        $response->assertSee('03-1234-5678');
    }

    public function test_about_page_renders_without_optional_fields(): void
    {
        Room::create([
            'site_id' => 'www',
            'sitename' => 'テストサイト',
            'site_joutai' => 1,
            'function_list' => '',
        ]);

        $this->get('/about')->assertOk()->assertSee('サイト紹介文はまだ登録されていません。');
    }

    public function test_legacy_aboutsite_asp_link_resolves_to_the_new_about_route(): void
    {
        Room::create([
            'site_id' => 'www', 'sitename' => 'テストサイト', 'site_joutai' => 1, 'function_list' => '',
        ]);
        TopMenu::create(['site_id' => 'www', 'menuname' => 'サイト概要', 'linkaddress' => 'aboutsite.asp', 'junban' => 1]);

        $response = $this->get('/')->assertOk();

        $response->assertSee('href="'.route('about').'"', false);
    }
}
