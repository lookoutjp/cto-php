<?php

namespace Tests\Feature;

use App\Filament\Resources\GuestbookCategoryResource\Pages\EditGuestbookCategory;
use App\Filament\Resources\InquiryResource\Pages\CreateInquiry;
use App\Filament\Resources\LinkItemResource\Pages\CreateLinkItem;
use App\Models\FileTag;
use App\Models\GuestbookCategory;
use App\Models\Inquiry;
use App\Models\LinkItem;
use App\Models\Member;
use App\Models\MemberRoom;
use App\Models\Room;
use App\Support\CurrentSite;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminFormFixesTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsManager(): void
    {
        Room::create(['site_id' => 'www', 'sitename' => 'テスト', 'site_joutai' => 1, 'function_list' => '']);
        $manager = Member::create(['member_id' => 'm1', 'name' => '管理太郎']);
        MemberRoom::create(['member_id' => 'm1', 'site_id' => 'www', 'ninshou' => -1]);

        $this->actingAs($manager);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_file_tags_list_page_loads_without_500(): void
    {
        $this->actingAsManager();
        FileTag::query()->getConnection()->table('file_tags')->insert([
            'site_id' => 'www', 'tag_id' => 5, 'tagname' => '既存タグ',
        ]);

        $this->get('/admin/file-tags')->assertOk();
    }

    public function test_creating_a_file_tag_auto_assigns_tag_id(): void
    {
        Room::create(['site_id' => 'www', 'sitename' => 'テスト', 'site_joutai' => 1, 'function_list' => '']);
        app(CurrentSite::class)->set('www');

        FileTag::query()->getConnection()->table('file_tags')->insert([
            'site_id' => 'www', 'tag_id' => 9, 'tagname' => '既存',
        ]);

        $tag = FileTag::create(['site_id' => 'www', 'tagname' => '新タグ']);

        $this->assertSame(10, (int) $tag->tag_id);
        $this->assertNotNull($tag->adddt);
    }

    public function test_inquiry_state_radio_maps_to_integer(): void
    {
        $this->actingAsManager();

        Livewire::test(CreateInquiry::class)
            ->fillForm(['customer_name' => '山田', 'state' => 2])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(2, (int) Inquiry::query()->where('customer_name', '山田')->firstOrFail()->state);
    }

    public function test_link_item_allow_checkbox_maps_to_string_flag(): void
    {
        $this->actingAsManager();

        Livewire::test(CreateLinkItem::class)
            ->fillForm(['name' => 'テストリンク', 'allow' => true])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame('1', (string) LinkItem::query()->where('name', 'テストリンク')->firstOrFail()->allow);
    }

    public function test_guestbook_category_member_list_round_trips_to_pipe_format(): void
    {
        $this->actingAsManager();
        foreach (['あ', 'い'] as $i => $n) {
            $mid = 'gm'.$i;
            Member::create(['member_id' => $mid, 'name' => $n]);
            MemberRoom::create(['member_id' => $mid, 'site_id' => 'www', 'ninshou' => 1]);
        }

        // id=1 は特別な「サイト掲示板」なので、フィラーを1件作ってから対象を作る
        GuestbookCategory::create(['site_id' => 'www', 'name' => 'サイト掲示板']);

        // 旧データがカンマ形式でも読み込めること
        $cat = GuestbookCategory::create(['site_id' => 'www', 'name' => 'コミュニティ', 'member' => 'gm0,gm1,']);

        Livewire::test(EditGuestbookCategory::class, ['record' => $cat->getKey()])
            ->assertFormSet(['member' => ['gm0', 'gm1']])
            ->fillForm(['member' => ['gm0']])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('||gm0||', $cat->fresh()->member);
        $this->assertSame(['gm0'], $cat->fresh()->allowedMemberIds());
    }
}
