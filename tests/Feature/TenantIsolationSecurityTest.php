<?php

namespace Tests\Feature;

use App\Filament\Resources\MemberRoomResource\Pages\CreateMemberRoom;
use App\Models\Member;
use App\Models\MemberRoom;
use App\Models\Room;
use App\Support\CurrentSite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * あるサイトの管理員が、Filament 管理画面から他サイト（本番含む）の
 * rooms / member_room を閲覧・編集・作成できてしまわないことを確認する。
 * member_room / rooms は BelongsToSite を使わないため、リソース側で
 * 明示的にスコープしないと横断アクセス（他サイトへの ninshou=-1 自己付与を含む）が可能だった。
 */
class TenantIsolationSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function makeSite(string $id): Room
    {
        return Room::create([
            'site_id' => $id,
            'sitename' => strtoupper($id),
            'site_joutai' => 1,
            'function_list' => '',
        ]);
    }

    private function makeManagerOf(string $siteId, string $memberId): Member
    {
        $m = Member::create(['member_id' => $memberId, 'name' => $memberId]);
        MemberRoom::create(['member_id' => $memberId, 'site_id' => $siteId, 'ninshou' => -1]);

        return $m;
    }

    public function test_manager_cannot_edit_another_tenants_room(): void
    {
        $this->makeSite('siteA');
        $this->makeSite('siteB');
        $mgrA = $this->makeManagerOf('siteA', 'mgrA');

        $this->actingAs($mgrA)->get('/admin/rooms/siteA/edit')->assertOk();
        $this->actingAs($mgrA)->get('/admin/rooms/siteB/edit')->assertNotFound();
    }

    public function test_only_super_admin_can_create_a_new_tenant(): void
    {
        $this->makeSite('siteA');
        $mgrA = $this->makeManagerOf('siteA', 'mgrA');

        $this->actingAs($mgrA)->get('/admin/rooms/create')->assertForbidden();
    }

    public function test_manager_cannot_edit_another_tenants_member_room(): void
    {
        $this->makeSite('siteA');
        $this->makeSite('siteB');
        $mgrA = $this->makeManagerOf('siteA', 'mgrA');
        $victim = $this->makeManagerOf('siteB', 'victimB');
        $ownRow = MemberRoom::where('site_id', 'siteA')->where('member_id', 'mgrA')->first();
        $otherRow = MemberRoom::where('site_id', 'siteB')->where('member_id', 'victimB')->first();

        $this->actingAs($mgrA)->get("/admin/member-rooms/{$ownRow->id}/edit")->assertOk();
        $this->actingAs($mgrA)->get("/admin/member-rooms/{$otherRow->id}/edit")->assertNotFound();
    }

    public function test_manager_cannot_self_escalate_to_admin_of_another_tenant_via_create(): void
    {
        $this->makeSite('siteA');
        $this->makeSite('www'); // 本番相当のターゲットテナント
        $mgrA = $this->makeManagerOf('siteA', 'mgrA');

        app(CurrentSite::class)->set('siteA');
        Member::create(['member_id' => 'attackerAlt', 'name' => 'attackerAlt']);

        // 攻撃者は別会員IDを使い、site_id=www, ninshou=-1 を指定して www の管理員を追加しようとする。
        Livewire::actingAs($mgrA)
            ->test(CreateMemberRoom::class)
            ->fillForm([
                'member_id' => 'attackerAlt',
                'site_id' => 'www',
                'ninshou' => -1,
            ])
            ->call('create');

        $this->assertFalse(
            MemberRoom::where('member_id', 'attackerAlt')->where('site_id', 'www')->exists(),
            'サイトAの管理員が www の member_room を作成できてしまっている（権限昇格）'
        );

        $created = MemberRoom::where('member_id', 'attackerAlt')->where('ninshou', -1)->first();
        $this->assertNotNull($created);
        $this->assertSame('siteA', $created->site_id, '作成された行が自サイトに固定されていない');
    }
}
