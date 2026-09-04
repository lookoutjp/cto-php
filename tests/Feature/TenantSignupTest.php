<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\MemberRoom;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * セルフサーブのテナント作成（新機能）。
 */
class TenantSignupTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'sitename' => 'アクメ株式会社',
            'site_id' => 'acme-corp',
            'name' => 'テスト太郎',
            'email' => 'founder@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], $overrides);
    }

    public function test_signup_creates_room_member_and_admin_membership(): void
    {
        $this->post('/signup', $this->validPayload())->assertRedirect('/admin');

        $room = Room::find('acme-corp');
        $this->assertNotNull($room);
        $this->assertSame('アクメ株式会社', $room->sitename);
        $this->assertTrue($room->hasFunction('wbsfunction'));
        $this->assertSame('free', $room->planKey());

        $member = Member::where('email', 'founder@example.com')->first();
        $this->assertNotNull($member);
        $this->assertAuthenticatedAs($member);

        $membership = MemberRoom::where('member_id', $member->member_id)->where('site_id', 'acme-corp')->first();
        $this->assertNotNull($membership);
        $this->assertSame(-1, (int) $membership->ninshou);
    }

    public function test_reserved_site_id_is_rejected(): void
    {
        $this->post('/signup', $this->validPayload(['site_id' => 'www']))
            ->assertSessionHasErrors('site_id');

        $this->assertGuest();
    }

    public function test_duplicate_site_id_is_rejected(): void
    {
        Room::create(['site_id' => 'acme-corp', 'sitename' => '先客', 'site_joutai' => 1, 'function_list' => '']);

        $this->post('/signup', $this->validPayload())->assertSessionHasErrors('site_id');
    }

    public function test_invalid_site_id_format_is_rejected(): void
    {
        $this->post('/signup', $this->validPayload(['site_id' => 'Has Spaces!']))
            ->assertSessionHasErrors('site_id');

        $this->post('/signup', $this->validPayload(['site_id' => 'ab']))
            ->assertSessionHasErrors('site_id');
    }

    public function test_new_member_can_immediately_access_admin_panel(): void
    {
        $this->post('/signup', $this->validPayload());

        $member = Member::where('email', 'founder@example.com')->first();
        $this->actingAs($member)->get('/admin')->assertOk();
    }
}
