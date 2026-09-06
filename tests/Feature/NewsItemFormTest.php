<?php

namespace Tests\Feature;

use App\Filament\Resources\NewsItemResource\Pages\CreateNewsItem;
use App\Filament\Resources\NewsItemResource\Pages\EditNewsItem;
use App\Models\Member;
use App\Models\MemberRoom;
use App\Models\NewsItem;
use App\Models\Room;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NewsItemFormTest extends TestCase
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

    public function test_form_hides_image_and_exposes_expected_fields(): void
    {
        $this->actingAsManager();

        Livewire::test(CreateNewsItem::class)
            ->assertFormFieldExists('title')
            ->assertFormFieldExists('content')
            ->assertFormFieldExists('istop')
            ->assertFormFieldExists('newsdate')
            ->assertFormFieldExists('adddatetime')
            ->assertFormFieldExists('editdatetime')
            ->assertFormFieldExists('clicks')
            ->assertFormFieldDoesNotExist('news_img');
    }

    public function test_istop_checkbox_maps_to_string_flag(): void
    {
        $this->actingAsManager();

        Livewire::test(CreateNewsItem::class)
            ->fillForm([
                'title' => 'ピン留めニュース',
                'istop' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $news = NewsItem::query()->where('title', 'ピン留めニュース')->firstOrFail();
        $this->assertSame('1', (string) $news->istop);
        $this->assertTrue($news->isPinned());

        // 既存レコードのチェックを外すと '0' になる
        Livewire::test(EditNewsItem::class, ['record' => $news->getKey()])
            ->assertFormSet(['istop' => true])
            ->fillForm(['istop' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('0', (string) $news->refresh()->istop);
        $this->assertFalse($news->isPinned());
    }
}
