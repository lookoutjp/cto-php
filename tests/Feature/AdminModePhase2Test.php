<?php

namespace Tests\Feature;

use App\Filament\Resources\ContentResource\Pages\CreateContent;
use App\Filament\Resources\ContentSortResource\Pages\CreateContentSort;
use App\Filament\Resources\FaqResource\Pages\EditFaq;
use App\Models\Content;
use App\Models\ContentSort;
use App\Models\Faq;
use App\Models\LinkItem;
use App\Models\Member;
use App\Models\MemberRoom;
use App\Models\NewsItem;
use App\Models\Room;
use App\Support\AdminMode;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * 管理者モード フェーズ2: コンテンツ / ニュース / FAQ / リンク集 の公開ページに
 * Filament の追加・編集への導線が出ることを確認する。
 */
class AdminModePhase2Test extends TestCase
{
    use RefreshDatabase;

    private function manager(string $functionList = ''): Member
    {
        Room::create([
            'site_id' => 'www', 'sitename' => 'テスト', 'site_joutai' => 1,
            'function_list' => $functionList,
        ]);
        $manager = Member::create(['member_id' => 'm1', 'name' => 'テスト管理者']);
        MemberRoom::create(['member_id' => 'm1', 'site_id' => 'www', 'ninshou' => -1]);

        return $manager;
    }

    public function test_faq_page_shows_edit_and_add_links_only_in_admin_mode(): void
    {
        $manager = $this->manager();
        $faq = Faq::create(['site_id' => 'www', 'question' => 'テスト質問', 'answer' => '回答', 'clicks' => 0]);

        $this->actingAs($manager)->get('/faq')->assertOk()
            ->assertDontSee('FAQを追加');

        AdminMode::enable('www');

        $this->actingAs($manager)->get('/faq')->assertOk()
            ->assertSee('FAQを追加')
            ->assertSee(route('filament.admin.resources.faqs.create'), false)
            ->assertSee(route('filament.admin.resources.faqs.edit', $faq), false);
    }

    public function test_contents_pages_show_content_and_category_links_in_admin_mode(): void
    {
        $manager = $this->manager();
        $category = ContentSort::create(['site_id' => 'www', 'name' => 'カテゴリA', 'junban' => 1]);
        $content = Content::create([
            'site_id' => 'www', 'content_sort' => $category->id, 'name' => '記事A', 'ok' => 1,
        ]);
        AdminMode::enable('www');

        // ツリー表示
        $this->actingAs($manager)->get('/contents')->assertOk()
            ->assertSee(route('filament.admin.resources.contents.create'), false)
            ->assertSee(route('filament.admin.resources.content-sorts.create'), false);

        // カテゴリ詳細
        $this->actingAs($manager)->get('/contents?category='.$category->id)->assertOk()
            ->assertSee(route('filament.admin.resources.content-sorts.edit', $category), false)
            ->assertSee(route('filament.admin.resources.contents.edit', $content), false)
            ->assertSee('このカテゴリに記事を追加');

        // 記事詳細
        $this->actingAs($manager)->get('/contents/'.$content->id)->assertOk()
            ->assertSee(route('filament.admin.resources.contents.edit', $content), false)
            ->assertSee('この記事を編集');
    }

    public function test_news_pages_show_edit_and_add_links_in_admin_mode(): void
    {
        $manager = $this->manager();
        $news = NewsItem::create([
            'site_id' => 'www', 'title' => 'お知らせ', 'content' => '本文',
            'newsdate' => now()->subDay(),
        ]);
        AdminMode::enable('www');

        $this->actingAs($manager)->get('/news')->assertOk()
            ->assertSee('ニュースを追加')
            ->assertSee(route('filament.admin.resources.news-items.edit', $news), false);

        $this->actingAs($manager)->get('/news/'.$news->id)->assertOk()
            ->assertSee('このニュースを編集')
            ->assertSee(route('filament.admin.resources.news-items.edit', $news), false);
    }

    public function test_links_page_shows_pending_links_and_edit_links_in_admin_mode(): void
    {
        $manager = $this->manager('friendlinkfunction');
        $pending = LinkItem::create(['site_id' => 'www', 'name' => '未承認サイト', 'allow' => 0]);

        // 通常表示では未承認リンクは出ない
        $this->actingAs($manager)->get('/links')->assertOk()
            ->assertDontSee('未承認サイト');

        AdminMode::enable('www');

        $this->actingAs($manager)->get('/links')->assertOk()
            ->assertSee('未承認サイト')
            ->assertSee('承認待ち')
            ->assertSee(route('filament.admin.resources.link-items.edit', $pending), false)
            ->assertSee(route('filament.admin.resources.link-items.create'), false);
    }

    public function test_create_content_prefills_category_from_query(): void
    {
        $manager = $this->manager();
        $category = ContentSort::create(['site_id' => 'www', 'name' => 'カテゴリA', 'junban' => 1]);

        $this->actingAs($manager);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Livewire::withQueryParams(['content_sort' => (string) $category->id]);

        Livewire::test(CreateContent::class)
            ->assertSet('data.content_sort', $category->id);
    }

    public function test_add_subcategory_from_category_page_prefills_current_category_as_parent(): void
    {
        $manager = $this->manager();
        $parent = ContentSort::create(['site_id' => 'www', 'name' => 'ナレッジ', 'junban' => 1]);
        $current = ContentSort::create([
            'site_id' => 'www', 'name' => '操作マニュアル', 'father_id' => $parent->id, 'junban' => 1,
        ]);
        AdminMode::enable('www');

        // カテゴリ詳細ページの「＋サブカテゴリを追加」は現在のカテゴリを親に指定する
        $this->actingAs($manager)->get('/contents?category='.$current->id)->assertOk()
            ->assertSee('サブカテゴリを追加')
            ->assertSee('father_id='.$current->id, false);

        // 作成ページは現在のカテゴリを親として事前選択する
        $this->actingAs($manager);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Livewire::withQueryParams(['father_id' => (string) $current->id]);

        Livewire::test(CreateContentSort::class)
            ->assertSet('data.father_id', $current->id);
    }

    public function test_editing_faq_with_back_param_redirects_after_save(): void
    {
        $manager = $this->manager();
        $faq = Faq::create(['site_id' => 'www', 'question' => '旧', 'answer' => '回答', 'clicks' => 0]);

        $this->actingAs($manager);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $back = 'http://localhost/faq';
        Livewire::withQueryParams(['back' => $back]);

        Livewire::test(EditFaq::class, ['record' => $faq->getKey()])
            ->assertSet('backTo', $back)
            ->fillForm(['question' => '新'])
            ->call('save')
            ->assertRedirect($back);
    }
}
