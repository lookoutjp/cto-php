<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 旧ASP由来の平文でない旧形式パスワードでもログインでき、成功時に静かに
 * bcrypt へ移行されることを確認する（LegacyAwareUserProvider）。
 */
class LegacyLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_with_legacy_password_can_log_in_and_gets_upgraded_to_bcrypt(): void
    {
        Room::create([
            'site_id' => 'www', 'sitename' => 'テスト', 'site_joutai' => 1, 'function_list' => '',
        ]);

        $plain = 'hunter2legacy';
        $legacyHash = substr(md5($plain), 0, 16);
        $member = Member::create([
            'member_id' => 'u1', 'name' => 'テスト太郎', 'email' => 'legacy@example.com',
            'password' => $legacyHash,
        ]);

        $this->post('/login', ['email' => 'legacy@example.com', 'password' => $plain])
            ->assertRedirect();

        $this->assertAuthenticatedAs($member->fresh());
        $this->assertTrue(str_starts_with($member->fresh()->password, '$2y$'), 'ログイン成功後に bcrypt へ移行されていない');
    }

    public function test_member_with_legacy_password_and_wrong_password_is_rejected(): void
    {
        Room::create([
            'site_id' => 'www', 'sitename' => 'テスト', 'site_joutai' => 1, 'function_list' => '',
        ]);

        Member::create([
            'member_id' => 'u1', 'name' => 'テスト太郎', 'email' => 'legacy@example.com',
            'password' => substr(md5('correct-password'), 0, 16),
        ]);

        $this->post('/login', ['email' => 'legacy@example.com', 'password' => 'wrong-password'])
            ->assertSessionHasErrors();

        $this->assertGuest();
    }
}
