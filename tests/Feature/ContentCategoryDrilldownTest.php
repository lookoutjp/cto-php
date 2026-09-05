<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\ContentSort;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * /contents の2カラム化（旧 contents.asp?Contentsort=N 相当）。
 */
class ContentCategoryDrilldownTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Room::create(['site_id' => 'www', 'sitename' => 'テスト', 'site_joutai' => 1, 'function_list' => '']);
    }

    public function test_category_drilldown_shows_own_and_descendant_contents_grouped_by_child(): void
    {
        $root = ContentSort::create(['site_id' => 'www', 'name' => 'ナレッジ', 'father_id' => 0]);
        $child = ContentSort::create(['site_id' => 'www', 'name' => '操作マニュアル', 'father_id' => $root->id]);
        $grandchild = ContentSort::create(['site_id' => 'www', 'name' => 'ユーザー向け', 'father_id' => $child->id]);

        Content::create(['site_id' => 'www', 'name' => 'ルート直下記事', 'content_sort' => $root->id, 'ok' => 1]);
        Content::create(['site_id' => 'www', 'name' => '孫カテゴリ記事', 'content_sort' => $grandchild->id, 'ok' => 1]);
        Content::create(['site_id' => 'www', 'name' => '非公開記事', 'content_sort' => $child->id, 'ok' => 0]);

        $response = $this->get("/contents?category={$root->id}")->assertOk();

        $response->assertSee('現在位置');
        $response->assertSee('ナレッジ');
        $response->assertSee('ルート直下記事');
        $response->assertSee('操作マニュアル');
        $response->assertSee('孫カテゴリ記事');
        $response->assertDontSee('非公開記事');
    }

    /**
     * 旧ASPのカテゴリ詳細画面は「現在位置」の直下にカテゴリの紹介文を出していた。
     * 新システムでも content_sorts.introduce をそこに表示する。
     */
    public function test_category_intro_text_shows_below_the_breadcrumb(): void
    {
        $category = ContentSort::create([
            'site_id' => 'www', 'name' => 'システム概要', 'father_id' => 0,
            'introduce' => '<p>CTOシステムは無料のシステムです。</p>',
        ]);

        $response = $this->get("/contents?category={$category->id}")->assertOk();

        $response->assertSeeInOrder(['現在位置', 'CTOシステムは無料のシステムです。'], false);
    }

    public function test_category_without_intro_text_shows_nothing_extra(): void
    {
        $category = ContentSort::create(['site_id' => 'www', 'name' => 'カテゴリ', 'father_id' => 0]);

        $this->get("/contents?category={$category->id}")->assertOk()
            ->assertDontSee('prose', false);
    }

    public function test_unknown_category_id_is_404(): void
    {
        $this->get('/contents?category=999999')->assertNotFound();
    }

    public function test_keyword_search_matches_name(): void
    {
        $cat = ContentSort::create(['site_id' => 'www', 'name' => 'カテゴリ', 'father_id' => 0]);
        Content::create(['site_id' => 'www', 'name' => 'WBS管理の基本知識', 'content_sort' => $cat->id, 'ok' => 1]);
        Content::create(['site_id' => 'www', 'name' => '関係ないタイトル', 'content_sort' => $cat->id, 'ok' => 1]);

        $response = $this->get('/contents?q=WBS')->assertOk();

        $response->assertSee('WBS管理の基本知識');
        $response->assertDontSee('関係ないタイトル');
    }
}
