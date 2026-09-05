<?php

namespace Tests\Feature;

use App\Filament\Resources\GuestbookResource\Pages\CreateGuestbook;
use App\Filament\Resources\MessageItemResource\Pages\CreateMessageItem;
use App\Models\Guestbook;
use App\Models\GuestbookCategory;
use App\Models\Member;
use App\Models\MemberRoom;
use App\Models\MessageItem;
use App\Models\Room;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BoardMessageFormTest extends TestCase
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

    public function test_guestbook_form_hides_internal_fields_and_defaults_space_num(): void
    {
        $this->actingAsManager();
        $cat = GuestbookCategory::create(['site_id' => 'www', 'name' => 'コミュニティ']);

        Livewire::test(CreateGuestbook::class)
            ->assertFormFieldExists('category')
            ->assertFormFieldExists('content')
            ->assertFormFieldExists('revert')
            ->assertFormFieldDoesNotExist('orders')
            ->assertFormFieldDoesNotExist('space_num')
            ->assertFormFieldDoesNotExist('top')
            ->fillForm(['category' => $cat->id, 'title' => 'テスト投稿', 'user_name' => 'm1'])
            ->call('create')
            ->assertHasNoFormErrors();

        $post = Guestbook::query()->where('title', 'テスト投稿')->firstOrFail();
        $this->assertSame(0, (int) $post->space_num);
        $this->assertSame($cat->id, (int) $post->category);
    }

    public function test_message_delete_flags_are_checkboxes_stored_as_boolean(): void
    {
        $this->actingAsManager();

        Livewire::test(CreateMessageItem::class)
            ->fillForm([
                'from' => 'm1', 'to' => 'm2',
                'delete_from' => true,
                'delete_to' => false,
                'readed' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $msg = MessageItem::query()->where('from', 'm1')->where('to', 'm2')->firstOrFail();
        $this->assertTrue((bool) $msg->delete_from);
        $this->assertFalse((bool) $msg->delete_to);
        $this->assertTrue((bool) $msg->readed);
    }

    public function test_message_model_defaults_delete_from_to_zero(): void
    {
        Room::create(['site_id' => 'www', 'sitename' => 'テスト', 'site_joutai' => 1, 'function_list' => '']);

        $msg = MessageItem::create(['site_id' => 'www', 'from' => 'a', 'to' => 'b']);
        $this->assertFalse((bool) $msg->delete_from);
    }
}
