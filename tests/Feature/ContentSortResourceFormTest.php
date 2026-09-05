<?php

namespace Tests\Feature;

use App\Filament\Resources\ContentSortResource\Pages\CreateContentSort;
use App\Filament\Resources\ContentSortResource\Pages\EditContentSort;
use App\Models\ContentSort;
use App\Models\Member;
use App\Models\MemberRoom;
use App\Models\Room;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * コンテンツカテゴリ編集フォームの再設計（親カテゴリ選択・権限ラジオ・
 * 個別公開設定/公開フラグのチェックボックス化）を検証する。
 */
class ContentSortResourceFormTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsManager(): void
    {
        Room::create(['site_id' => 'www', 'sitename' => 'テスト', 'site_joutai' => 1, 'function_list' => '']);
        $manager = Member::create(['member_id' => 'm1', 'name' => 'テスト']);
        MemberRoom::create(['member_id' => 'm1', 'site_id' => 'www', 'ninshou' => -1]);

        $this->actingAs($manager);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_create_maps_radio_and_checkboxes_to_legacy_values(): void
    {
        $this->actingAsManager();

        Livewire::test(CreateContentSort::class)
            ->fillForm([
                'name' => '新カテゴリ',
                'father_id' => 0,
                'ninshou' => '1',          // ユーザ
                'ninshouspecial' => false, // 管理員だけに見える
                'koukaiflag' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $cat = ContentSort::query()->where('name', '新カテゴリ')->firstOrFail();
        $this->assertSame(1, (int) $cat->ninshou);
        $this->assertSame('0', (string) $cat->ninshouspecial);
        $this->assertSame(1, (int) $cat->koukaiflag);
    }

    public function test_guest_permission_is_stored_as_null(): void
    {
        $this->actingAsManager();

        Livewire::test(CreateContentSort::class)
            ->fillForm(['name' => 'ゲスト向け', 'father_id' => 0, 'ninshou' => '', 'ninshouspecial' => true, 'koukaiflag' => true])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertNull(ContentSort::query()->where('name', 'ゲスト向け')->firstOrFail()->ninshou);
    }

    public function test_edit_form_loads_legacy_values_into_the_new_controls(): void
    {
        $this->actingAsManager();
        $cat = ContentSort::create([
            'site_id' => 'www', 'name' => '既存', 'father_id' => 0,
            'ninshou' => -1, 'ninshouspecial' => ',,', 'koukaiflag' => 0,
        ]);

        Livewire::test(EditContentSort::class, ['record' => $cat->getKey()])
            ->assertFormSet([
                'ninshou' => '-1',
                'ninshouspecial' => true, // 旧データ ',,' は「公開(チェック)」扱い
                'koukaiflag' => false,
            ]);
    }

    public function test_parent_options_exclude_self_and_descendants(): void
    {
        $this->actingAsManager();
        $root = ContentSort::create(['site_id' => 'www', 'name' => '親', 'father_id' => 0]);
        $child = ContentSort::create(['site_id' => 'www', 'name' => '子', 'father_id' => $root->id]);

        // 親を編集するとき、選択肢に自分自身と子孫が出ないこと
        $component = Livewire::test(EditContentSort::class, ['record' => $root->getKey()]);
        $options = $component->instance()->form->getComponent('data.father_id')->getOptions();

        $this->assertArrayHasKey(0, $options);          // ルートは選べる
        $this->assertArrayNotHasKey($root->id, $options); // 自分自身は出ない
        $this->assertArrayNotHasKey($child->id, $options); // 子孫も出ない
    }
}
