<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\MemberRoom;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 公開フロントのヘッダー右上（旧 inc_top.asp の会員メニュー相当）:
 * ログイン中は名前（displayName）のドロップダウン、dengonfunction 有効なら
 * メッセージアイコンも出す。ゲストは「ログイン」リンク。
 */
class PublicHeaderUserMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_logged_in_member_sees_name_and_id_and_messages_link(): void
    {
        Room::create([
            'site_id' => 'www', 'sitename' => 'テスト', 'site_joutai' => 1,
            'function_list' => 'dengonfunction',
        ]);
        $member = Member::create(['member_id' => 'taro-001', 'name' => 'サンプル太郎']);
        MemberRoom::create(['member_id' => 'taro-001', 'site_id' => 'www', 'ninshou' => 1]);

        $this->actingAs($member)->get('/')->assertOk()
            ->assertSee('サンプル太郎')
            ->assertDontSee('taro-001')
            ->assertSee(route('messages.index'), false)
            ->assertSee(route('profile.edit'), false)
            ->assertDontSee('ログイン');
    }

    public function test_messages_link_hidden_without_dengon_function(): void
    {
        Room::create([
            'site_id' => 'www', 'sitename' => 'テスト', 'site_joutai' => 1, 'function_list' => '',
        ]);
        $member = Member::create(['member_id' => 'taro-002', 'name' => 'サンプル次郎']);
        MemberRoom::create(['member_id' => 'taro-002', 'site_id' => 'www', 'ninshou' => 1]);

        $this->actingAs($member)->get('/')->assertOk()
            ->assertSee('サンプル次郎')
            ->assertDontSee(route('messages.index'), false);
    }

    public function test_guest_sees_login_link_only(): void
    {
        Room::create([
            'site_id' => 'www', 'sitename' => 'テスト', 'site_joutai' => 1, 'function_list' => '',
        ]);

        $this->get('/')->assertOk()
            ->assertSee(route('login'), false)
            ->assertDontSee(route('profile.edit'), false);
    }
}
