<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\MemberRoom;
use App\Models\Room;
use App\Support\CurrentSite;
use App\Support\Plans;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * 課金まわりの実効性（プラン上限・滞納ブロック）を確認する。
 * 実際に金額が絡む・書き込みブロックに直結するロジックなのでテストしておく。
 */
class BillingEnforcementTest extends TestCase
{
    use RefreshDatabase;

    private function makeRoom(string $id = 'www'): Room
    {
        return Room::create([
            'site_id' => $id,
            'sitename' => 'テスト',
            'site_joutai' => 1,
            'function_list' => 'todofunction',
        ]);
    }

    public function test_free_plan_member_limit_is_enforced(): void
    {
        $room = $this->makeRoom();
        $limit = Plans::get('free')['limits']['members'];

        for ($i = 0; $i < $limit; $i++) {
            MemberRoom::create(['member_id' => "m{$i}", 'site_id' => $room->getKey(), 'ninshou' => 0]);
        }

        $this->assertTrue(Plans::withinMemberLimit($room, 0));
        $this->assertFalse(Plans::withinMemberLimit($room, 1), '上限ちょうどの状態であと1人追加できてしまっている');
    }

    public function test_free_plan_storage_limit_is_enforced(): void
    {
        $room = $this->makeRoom();
        $limitBytes = Plans::get('free')['limits']['storage_mb'] * 1024 * 1024;

        DB::table('files')->insert([
            'site_id' => $room->getKey(), 'filename' => 'a', 'fileext' => 'pdf',
            'size_bytes' => $limitBytes - 100,
        ]);

        $this->assertTrue(Plans::withinStorageLimit($room, 0));
        $this->assertFalse(Plans::withinStorageLimit($room, 200), '上限を超える追加が許可されてしまっている');
    }

    public function test_room_with_no_subscription_is_free_and_not_delinquent(): void
    {
        $room = $this->makeRoom();

        $this->assertSame('free', $room->planKey());
        $this->assertFalse($room->billingIsDelinquent());
    }

    public function test_past_due_subscription_marks_room_delinquent(): void
    {
        $room = $this->makeRoom();
        DB::table('subscriptions')->insert([
            'room_site_id' => $room->getKey(), 'type' => 'default',
            'stripe_id' => 'sub_test', 'stripe_status' => 'past_due', 'stripe_price' => 'price_x',
            'quantity' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->assertTrue($room->fresh()->billingIsDelinquent());
    }

    public function test_delinquent_tenant_blocks_writes_but_allows_reads(): void
    {
        $room = $this->makeRoom();
        DB::table('subscriptions')->insert([
            'room_site_id' => $room->getKey(), 'type' => 'default',
            'stripe_id' => 'sub_test', 'stripe_status' => 'past_due', 'stripe_price' => 'price_x',
            'quantity' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $member = Member::create(['member_id' => 'm1', 'name' => 'M1']);
        MemberRoom::create(['member_id' => 'm1', 'site_id' => $room->getKey(), 'ninshou' => 1]);
        app(CurrentSite::class)->set($room->getKey());

        $this->actingAs($member)->get('/tasks/todo')->assertOk();
        $this->actingAs($member)->post('/tasks/todo', ['title' => 'x'])
            ->assertStatus(402);
    }
}
