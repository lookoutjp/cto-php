<?php

namespace Tests\Feature;

use App\Filament\Resources\ContentCommentResource\Pages\CreateContentComment;
use App\Filament\Resources\FaqResource\Pages\CreateFaq;
use App\Models\ContentComment;
use App\Models\Faq;
use App\Models\Member;
use App\Models\MemberRoom;
use App\Models\Room;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CommentAndFaqFormTest extends TestCase
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

    public function test_content_comment_created_via_form_defaults_ninshou_to_zero_without_the_field(): void
    {
        $this->actingAsManager();

        Livewire::test(CreateContentComment::class)
            ->assertFormExists()
            ->fillForm(['name' => 'コメントのタイトル', 'comment' => '本文', 'member_id' => 'm1'])
            ->call('create')
            ->assertHasNoFormErrors();

        $comment = ContentComment::query()->where('name', 'コメントのタイトル')->firstOrFail();
        $this->assertSame(0, (int) $comment->ninshou);
    }

    public function test_content_comment_form_has_no_permission_field(): void
    {
        $this->actingAsManager();

        Livewire::test(CreateContentComment::class)
            ->assertFormFieldExists('name')
            ->assertFormFieldExists('comment')
            ->assertFormFieldExists('member_id')
            ->assertFormFieldExists('time')
            ->assertFormFieldDoesNotExist('ninshou')
            ->assertFormFieldDoesNotExist('content_id');
    }

    public function test_model_default_attribute_sets_ninshou_zero(): void
    {
        Room::create(['site_id' => 'www', 'sitename' => 'テスト', 'site_joutai' => 1, 'function_list' => '']);

        $c = ContentComment::create(['site_id' => 'www', 'name' => 'x', 'comment' => 'y']);
        $this->assertSame(0, (int) $c->ninshou);
    }

    public function test_faq_form_field_order_and_label(): void
    {
        $this->actingAsManager();

        Livewire::test(CreateFaq::class)
            ->assertFormFieldExists('question')
            ->assertFormFieldExists('answer')
            ->assertFormFieldExists('clicks')
            ->fillForm(['question' => 'これは質問？', 'answer' => 'これは回答。'])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertTrue(Faq::query()->where('question', 'これは質問？')->exists());
    }
}
