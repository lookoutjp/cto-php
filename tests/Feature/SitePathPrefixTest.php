<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\MemberRoom;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 独自ドメインを使わず、共有ドメイン上で /{site}/ からテナントの公開フロントを開く。
 */
class SitePathPrefixTest extends TestCase
{
    use RefreshDatabase;

    private function makeSites(): void
    {
        Room::create(['site_id' => 'www', 'sitename' => 'CTO', 'site_joutai' => 1, 'function_list' => '']);
        Room::create(['site_id' => 'demo', 'sitename' => 'CtoS', 'site_joutai' => 1, 'function_list' => '']);
        Room::create(['site_id' => 'miraipm', 'sitename' => 'ミライPM', 'site_joutai' => 1, 'function_list' => '']);
    }

    public function test_guest_prefix_switches_public_front_to_that_site(): void
    {
        $this->makeSites();

        $this->get('/demo/')
            ->assertRedirect('/')
            ->assertSessionHas('site_view', 'demo');

        // 以降 / はそのサイトの公開フロント（サイト名で判定）
        $this->get('/')->assertOk()->assertSee('CtoS');
    }

    public function test_prefix_with_path_and_query_is_preserved(): void
    {
        $this->makeSites();

        $this->get('/demo/faq')->assertRedirect('/faq');
        $this->get('/demo/contents?q=abc')->assertRedirect('/contents?q=abc');
    }

    public function test_unknown_site_prefix_is_404(): void
    {
        $this->makeSites();

        $this->get('/no-such-tenant/')->assertNotFound();
    }

    public function test_member_can_view_a_site_they_do_not_belong_to(): void
    {
        $this->makeSites();
        $member = Member::create(['member_id' => 'm1', 'name' => 'www会員']);
        MemberRoom::create(['member_id' => 'm1', 'site_id' => 'www', 'ninshou' => 0]);

        $this->actingAs($member)->get('/miraipm/')->assertRedirect('/');
        $this->actingAs($member)->get('/')->assertOk()->assertSee('ミライPM');

        // ただし所属していないので管理・プロジェクト機能は不可のまま
        $this->assertFalse($member->fresh()->managesSite('miraipm'));
        $this->assertFalse($member->fresh()->isProjectMemberOf('miraipm'));
    }

    public function test_reserved_prefixes_are_not_shadowed(): void
    {
        $this->makeSites();

        // admin パネルのログインページは従来どおり（site.enter に食われない）
        $this->get('/admin/login')->assertOk();
    }
}
