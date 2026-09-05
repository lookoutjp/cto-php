<?php

namespace Tests\Feature;

use App\Filament\Resources\ContentSortResource\Pages\CreateContentSort;
use App\Filament\Resources\ContentSortResource\Pages\EditContentSort;
use App\Filament\Resources\TopMenuResource\Pages\CreateTopMenu;
use App\Filament\Resources\TopMenuResource\Pages\EditTopMenu;
use App\Models\ContentSort;
use App\Models\Member;
use App\Models\MemberRoom;
use App\Models\Room;
use App\Models\TopMenu;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * 管理者モードのインライン編集リンク（?back=元ページURL）から Filament の
 * 作成/編集画面に入った場合、保存後に元のページへ戻ることを確認する。
 */
class AdminModeBackRedirectTest extends TestCase
{
    use RefreshDatabase;

    private function loginAsManager(): Member
    {
        Room::create(['site_id' => 'www', 'sitename' => 'テスト', 'site_joutai' => 1, 'function_list' => '']);
        $manager = Member::create(['member_id' => 'm1', 'name' => 'テスト']);
        MemberRoom::create(['member_id' => 'm1', 'site_id' => 'www', 'ninshou' => -1]);

        $this->actingAs($manager);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        return $manager;
    }

    /**
     * Livewire::test() 経由でマウントされるコンポーネントに ?back= を
     * 届けるには、Livewire::withQueryParams() でテスト用クエリを予約する。
     */
    private function setBackQueryParam(?string $back): void
    {
        Livewire::withQueryParams($back !== null ? ['back' => $back] : []);
    }

    public function test_editing_top_menu_with_back_param_redirects_to_it_after_save(): void
    {
        $this->loginAsManager();
        $tm = TopMenu::create(['site_id' => 'www', 'menuname' => '旧名', 'junban' => 1]);

        $back = 'http://localhost/';
        $this->setBackQueryParam($back);

        Livewire::test(EditTopMenu::class, ['record' => $tm->getKey()])
            ->assertSet('backTo', $back)
            ->fillForm(['menuname' => '新名'])
            ->call('save')
            ->assertRedirect($back);

        $this->assertSame('新名', $tm->fresh()->menuname);
    }

    public function test_editing_top_menu_without_back_param_keeps_default_behaviour(): void
    {
        $this->loginAsManager();
        $tm = TopMenu::create(['site_id' => 'www', 'menuname' => '旧名', 'junban' => 1]);

        Livewire::test(EditTopMenu::class, ['record' => $tm->getKey()])
            ->fillForm(['menuname' => '新名'])
            ->call('save')
            ->assertNoRedirect();
    }

    public function test_editing_top_menu_rejects_an_external_back_url(): void
    {
        $this->loginAsManager();
        $tm = TopMenu::create(['site_id' => 'www', 'menuname' => '旧名', 'junban' => 1]);

        $this->setBackQueryParam('https://evil.example.com/phish');

        Livewire::test(EditTopMenu::class, ['record' => $tm->getKey()])
            ->fillForm(['menuname' => '新名'])
            ->call('save')
            ->assertNoRedirect();
    }

    public function test_creating_top_menu_with_back_param_redirects_to_it(): void
    {
        $this->loginAsManager();

        $back = 'http://localhost/';
        $this->setBackQueryParam($back);

        Livewire::test(CreateTopMenu::class)
            ->fillForm(['menuname' => '新規メニュー', 'junban' => 9])
            ->call('create')
            ->assertRedirect($back);

        $this->assertDatabaseHas('top_menus', ['menuname' => '新規メニュー']);
    }

    public function test_editing_content_sort_with_back_param_redirects_to_it_after_save(): void
    {
        $this->loginAsManager();
        $cat = ContentSort::create(['site_id' => 'www', 'name' => '旧カテゴリ', 'junban' => 1]);

        $back = 'http://localhost/';
        $this->setBackQueryParam($back);

        Livewire::test(EditContentSort::class, ['record' => $cat->getKey()])
            ->fillForm(['name' => '新カテゴリ'])
            ->call('save')
            ->assertRedirect($back);

        $this->assertSame('新カテゴリ', $cat->fresh()->name);
    }

    public function test_creating_content_sort_with_back_param_redirects_to_it(): void
    {
        $this->loginAsManager();

        $back = 'http://localhost/';
        $this->setBackQueryParam($back);

        Livewire::test(CreateContentSort::class)
            ->fillForm(['name' => '新カテゴリ'])
            ->call('create')
            ->assertRedirect($back);

        $this->assertDatabaseHas('content_sorts', ['name' => '新カテゴリ']);
    }
}
