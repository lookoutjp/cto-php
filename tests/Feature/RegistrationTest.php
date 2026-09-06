<?php

namespace Tests\Feature;

use App\Filament\Resources\MemberResource\Pages\ListMembers;
use App\Models\Member;
use App\Models\MemberRoom;
use App\Models\Room;
use App\Support\CurrentSite;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * 会員登録（/register）: 会員はプラットフォーム全体で一元管理、登録したサイトへは
 * 「承認待ち」で加入し、members.signup_site に登録元を記録する。
 */
class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function site(string $id, bool $registration = true): Room
    {
        return Room::create([
            'site_id' => $id, 'sitename' => strtoupper($id), 'site_joutai' => 1,
            'function_list' => $registration ? 'newmemberregfunction' : '',
        ]);
    }

    public function test_registration_link_shows_only_when_enabled(): void
    {
        $this->site('www');
        app(CurrentSite::class)->set('www');

        $this->get('/login')->assertOk()->assertSee(route('register'), false);
        $this->get('/')->assertOk()->assertSee(route('register'), false);
    }

    public function test_registration_link_hidden_without_function(): void
    {
        $this->site('www', registration: false);
        app(CurrentSite::class)->set('www');

        $this->get('/login')->assertOk()->assertDontSee(route('register'), false);
    }

    public function test_register_creates_global_member_and_pending_membership(): void
    {
        $this->site('demo');

        // /demo/ プレフィックス経由で demo を表示中サイトにしてから登録する
        $this->withSession(['site_view' => 'demo'])->post('/register', [
            'name' => '登録太郎',
            'email' => 'toroku@example.test',
            'password' => 'cto-local-test-2026',
            'password_confirmation' => 'cto-local-test-2026',
        ])->assertRedirect(route('dashboard'));

        $member = Member::where('email', 'toroku@example.test')->firstOrFail();
        $this->assertSame('demo', $member->signup_site);
        $this->assertAuthenticatedAs($member);

        $room = MemberRoom::withoutGlobalScope('confirmed')
            ->where('member_id', $member->member_id)->where('site_id', 'demo')->firstOrFail();
        $this->assertNull($room->ninshou);
        $this->assertNotNull($room->applied_at);
        $this->assertTrue($room->isPending());

        // 承認待ちなのでプロジェクト機能は不可、mypage は簡易版＋承認待ち表示
        $this->assertFalse($member->fresh()->isProjectMemberOf('demo'));
        $this->withSession(['site_view' => 'demo'])->get('/mypage')
            ->assertOk()->assertSee('承認待ち');
    }

    public function test_member_list_is_global_for_default_site_but_scoped_elsewhere(): void
    {
        $this->site('www');
        $this->site('demo');
        $this->site('miraipm');

        $manager = Member::create(['member_id' => 'boss', 'name' => '管理者', 'signup_site' => 'www']);
        MemberRoom::create(['member_id' => 'boss', 'site_id' => 'www', 'ninshou' => -1]);
        MemberRoom::create(['member_id' => 'boss', 'site_id' => 'demo', 'ninshou' => -1]);

        $demoMember = Member::create(['member_id' => 'd1', 'name' => 'demo会員', 'signup_site' => 'demo']);
        MemberRoom::create(['member_id' => 'd1', 'site_id' => 'demo', 'ninshou' => 0]);

        $miraiMember = Member::create(['member_id' => 'p1', 'name' => 'mirai会員', 'signup_site' => 'miraipm']);
        MemberRoom::create(['member_id' => 'p1', 'site_id' => 'miraipm', 'ninshou' => 0]);

        $this->actingAs($manager);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        // 既定サイト（www）では全会員が見える
        app(CurrentSite::class)->set('www');
        Livewire::test(ListMembers::class)
            ->assertCanSeeTableRecords([$manager, $demoMember, $miraiMember]);

        // demo の管理画面では demo に紐づく会員のみ
        app(CurrentSite::class)->set('demo');
        Livewire::test(ListMembers::class)
            ->assertCanSeeTableRecords([$manager, $demoMember])
            ->assertCanNotSeeTableRecords([$miraiMember]);
    }
}
