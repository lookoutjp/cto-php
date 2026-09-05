<?php

namespace Tests\Feature;

use App\Filament\Resources\ContentResource\Pages\CreateContent;
use App\Filament\Resources\ContentResource\Pages\EditContent;
use App\Models\Content;
use App\Models\ContentSort;
use App\Models\Member;
use App\Models\MemberRoom;
use App\Models\Room;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * コンテンツ編集フォームの再設計（カテゴリ選択・投稿者選択・コメント設定/
 * 公開設定のラジオ化・本文リッチテキスト）を検証する。
 */
class ContentResourceFormTest extends TestCase
{
    use RefreshDatabase;

    private ContentSort $category;

    private function actingAsManager(): Member
    {
        Room::create(['site_id' => 'www', 'sitename' => 'テスト', 'site_joutai' => 1, 'function_list' => '']);
        $this->category = ContentSort::create(['site_id' => 'www', 'name' => '管理者向け', 'father_id' => 0]);

        $manager = Member::create(['member_id' => 'm1', 'name' => '管理太郎']);
        MemberRoom::create(['member_id' => 'm1', 'site_id' => 'www', 'ninshou' => -1]);

        $this->actingAs($manager);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        return $manager;
    }

    public function test_create_maps_radios_to_legacy_values_and_defaults_owner_to_current_user(): void
    {
        $this->actingAsManager();

        Livewire::test(CreateContent::class)
            ->assertFormSet(['owner' => 'm1']) // 投稿者の既定は今のユーザー
            ->fillForm([
                'content_sort' => $this->category->id,
                'name' => '初期構築手順',
                'commentok' => 0,
                'ok' => '1', // 公開済み
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $content = Content::query()->where('name', '初期構築手順')->firstOrFail();
        $this->assertSame($this->category->id, (int) $content->content_sort);
        $this->assertSame(0, (int) $content->commentok);
        $this->assertSame(1, (int) $content->ok);
        $this->assertSame('m1', $content->owner);
    }

    public function test_pending_review_status_is_stored_as_two(): void
    {
        $this->actingAsManager();

        Livewire::test(CreateContent::class)
            ->fillForm(['content_sort' => $this->category->id, 'name' => '審査待ち記事', 'ok' => '2', 'commentok' => 1])
            ->call('create')
            ->assertHasNoFormErrors();

        $content = Content::query()->where('name', '審査待ち記事')->firstOrFail();
        $this->assertSame(2, (int) $content->ok);
        $this->assertSame(1, (int) $content->commentok);
        // 公開スコープには出ない（ok=1 のみ公開）
        $this->assertFalse(Content::query()->published()->where('name', '審査待ち記事')->exists());
    }

    public function test_edit_form_loads_legacy_binary_values(): void
    {
        $this->actingAsManager();
        $content = Content::create([
            'site_id' => 'www', 'content_sort' => $this->category->id, 'name' => '既存記事',
            'ok' => 1, 'commentok' => 1, 'recommend' => 1, 'owner' => 'm1',
        ]);

        Livewire::test(EditContent::class, ['record' => $content->getKey()])
            ->assertFormSet([
                'ok' => '1',
                'commentok' => 1,
                'recommend' => true,
            ]);
    }

    public function test_category_options_are_hierarchical(): void
    {
        $this->actingAsManager();
        $child = ContentSort::create(['site_id' => 'www', 'name' => '子カテゴリ', 'father_id' => $this->category->id]);

        $options = Livewire::test(CreateContent::class)
            ->instance()->form->getComponent('data.content_sort')->getOptions();

        $this->assertArrayHasKey($this->category->id, $options);
        $this->assertArrayHasKey($child->id, $options);
        $this->assertStringContainsString('└', $options[$child->id]); // 子はインデント記号付き
    }
}
