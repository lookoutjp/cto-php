<?php

namespace Tests\Feature;

use App\Filament\Resources\MemberRoomResource\Pages\ListMemberRooms;
use App\Models\Member;
use App\Models\MemberRoom;
use App\Models\Room;
use App\Support\CurrentSite;
use App\Support\MemberOptions;
use App\Support\Plans;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * 既存会員が別サイトへ加入申請 → 管理員が「会員権限」画面で承認 / 却下するフロー。
 */
class SiteJoinTest extends TestCase
{
    use RefreshDatabase;

    private function makeSite(string $id, string $functions = 'newmemberregfunction'): Room
    {
        return Room::create([
            'site_id' => $id, 'sitename' => strtoupper($id), 'site_joutai' => 1,
            'function_list' => $functions,
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/join')->assertRedirect(route('login'));
    }

    public function test_member_can_submit_a_join_request(): void
    {
        $this->makeSite('www');
        $this->makeSite('miraipm');

        $member = Member::create(['member_id' => 'm1', 'name' => '会員太郎']);
        MemberRoom::create(['member_id' => 'm1', 'site_id' => 'www', 'ninshou' => 0]);

        $this->actingAs($member)->get('/join')->assertOk()->assertSee('MIRAIPM');

        $this->actingAs($member)->post(route('site-join.store', 'miraipm'))
            ->assertRedirect()
            ->assertSessionHas('status');

        $row = MemberRoom::withoutGlobalScope('confirmed')
            ->where('member_id', 'm1')->where('site_id', 'miraipm')->first();

        $this->assertNotNull($row);
        $this->assertNull($row->ninshou);
        $this->assertNotNull($row->applied_at);
        $this->assertNull($row->approved_at);
        $this->assertTrue($row->isPending());
    }

    public function test_pending_request_is_hidden_from_normal_queries_but_site_is_accessible(): void
    {
        $this->makeSite('www');
        $this->makeSite('miraipm');

        $member = Member::create(['member_id' => 'm1', 'name' => '会員太郎']);
        MemberRoom::create(['member_id' => 'm1', 'site_id' => 'www', 'ninshou' => 0]);
        $this->actingAs($member)->post(route('site-join.store', 'miraipm'));

        // 通常のクエリからは除外される
        $this->assertSame(0, MemberRoom::query()->where('site_id', 'miraipm')->count());

        // 承認待ちは参加者でも管理員でもない
        $this->assertFalse($member->fresh()->isProjectMemberOf('miraipm'));
        $this->assertFalse($member->fresh()->managesSite('miraipm'));

        // ただし公開コンテンツ閲覧のため「所属サイト」には含まれる
        $this->assertTrue($member->fresh()->accessibleSiteIds()->contains('miraipm'));
        $this->assertTrue($member->fresh()->pendingSiteIds()->contains('miraipm'));
    }

    public function test_cannot_request_twice_or_a_site_already_joined(): void
    {
        $this->makeSite('www');
        $member = Member::create(['member_id' => 'm1', 'name' => '会員太郎']);
        MemberRoom::create(['member_id' => 'm1', 'site_id' => 'www', 'ninshou' => 1]);

        $this->actingAs($member)->post(route('site-join.store', 'www'))->assertRedirect();

        // 参加中なので新しい行は作られない
        $this->assertSame(1, MemberRoom::withoutGlobalScope('confirmed')
            ->where('member_id', 'm1')->where('site_id', 'www')->count());
    }

    public function test_member_options_and_plan_usage_exclude_pending(): void
    {
        $site = $this->makeSite('www');
        $confirmed = Member::create(['member_id' => 'yes', 'name' => '承認済み会員']);
        MemberRoom::create(['member_id' => 'yes', 'site_id' => 'www', 'ninshou' => 1]);

        $pending = Member::create(['member_id' => 'no', 'name' => '申請中会員']);
        MemberRoom::withoutGlobalScope('confirmed')->create([
            'member_id' => 'no', 'site_id' => 'www', 'ninshou' => null, 'applied_at' => now(),
        ]);

        app(CurrentSite::class)->set('www');

        $options = MemberOptions::forCurrentSite();
        $this->assertArrayHasKey('yes', $options);
        $this->assertArrayNotHasKey('no', $options);

        $this->assertSame(1, Plans::memberUsage($site));
    }

    public function test_manager_can_approve_and_reject_in_filament(): void
    {
        $this->makeSite('www');
        $manager = Member::create(['member_id' => 'boss', 'name' => '管理者']);
        MemberRoom::create(['member_id' => 'boss', 'site_id' => 'www', 'ninshou' => -1]);

        $applicant = Member::create(['member_id' => 'app1', 'name' => '申請者']);
        $req = MemberRoom::withoutGlobalScope('confirmed')->create([
            'member_id' => 'app1', 'site_id' => 'www', 'ninshou' => null, 'applied_at' => now(),
        ]);

        $this->actingAs($manager);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        app(CurrentSite::class)->set('www');

        Livewire::test(ListMemberRooms::class)
            ->assertCanSeeTableRecords([$req])
            ->callTableAction('approve', $req, data: ['ninshou' => '1']);

        $req->refresh();
        $this->assertSame(1, (int) $req->ninshou);
        $this->assertNotNull($req->approved_at);
        $this->assertFalse($req->isPending());
        $this->assertTrue($applicant->fresh()->isProjectMemberOf('www'));

        // 別の申請を却下すると行が消える
        $req2 = MemberRoom::withoutGlobalScope('confirmed')->create([
            'member_id' => 'app2', 'site_id' => 'www', 'ninshou' => null, 'applied_at' => now(),
        ]);
        Member::create(['member_id' => 'app2', 'name' => '申請者2']);

        Livewire::test(ListMemberRooms::class)
            ->callTableAction('reject', $req2);

        $this->assertDatabaseMissing('member_room', ['member_id' => 'app2', 'site_id' => 'www']);
    }
}
