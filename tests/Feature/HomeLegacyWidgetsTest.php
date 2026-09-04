<?php

namespace Tests\Feature;

use App\Models\ContentSort;
use App\Models\Room;
use App\Models\TopMenu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * トップページの旧ASP由来のウィジェット（top_menus のボタン列、content_sorts の
 * トップレベルをカテゴリサイドバーとして表示）が正しく出ることを確認する。
 */
class HomeLegacyWidgetsTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_renders_top_menu_bar_and_category_sidebar(): void
    {
        Room::create([
            'site_id' => 'www', 'sitename' => 'テスト', 'site_joutai' => 1, 'function_list' => '',
        ]);

        TopMenu::create(['site_id' => 'www', 'junban' => 1, 'menuname' => 'お問合せ', 'linkaddress' => 'otoi.asp']);
        TopMenu::create(['site_id' => 'www', 'junban' => 2, 'menuname' => 'デモ', 'linkaddress' => 'https://demo.example.com']);

        ContentSort::create(['site_id' => 'www', 'name' => 'よくある質問', 'father_id' => 0, 'link' => 'faq.asp']);
        ContentSort::create(['site_id' => 'www', 'name' => '非公開カテゴリ', 'father_id' => 0, 'ninshou' => 1]);

        $response = $this->get('/')->assertOk();

        $response->assertSee('お問合せ');
        $response->assertSee(route('contact.create'), false);
        $response->assertSee('https://demo.example.com', false);

        $response->assertSee('よくある質問');
        $response->assertSee(route('faq.index'), false);
        $response->assertDontSee('非公開カテゴリ');
    }

    public function test_home_renders_without_legacy_widgets_when_no_data(): void
    {
        Room::create([
            'site_id' => 'www', 'sitename' => 'テスト', 'site_joutai' => 1, 'function_list' => '',
        ]);

        $this->get('/')->assertOk()->assertDontSee('カテゴリ');
    }
}
