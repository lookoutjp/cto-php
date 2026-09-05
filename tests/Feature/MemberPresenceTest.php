<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\MemberRoom;
use App\Models\Room;
use App\Support\CurrentSite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberPresenceTest extends TestCase
{
    use RefreshDatabase;

    private function site(string $functions = ''): Room
    {
        return Room::create([
            'site_id' => 'www',
            'sitename' => 'テスト',
            'site_joutai' => 1,
            'function_list' => $functions,
        ]);
    }

    private function participant(string $id, ?string $name = null): Member
    {
        $m = Member::create(['member_id' => $id, 'name' => $name ?? strtoupper($id)]);
        MemberRoom::create(['member_id' => $id, 'site_id' => 'www', 'ninshou' => 1]);

        return $m;
    }

    public function test_request_refreshes_presence_timestamp(): void
    {
        $this->site('memberlistfunction');
        $m = $this->participant('u1');
        $this->assertNull($m->timerenew);

        $this->actingAs($m)->get('/members')->assertOk();

        $this->assertTrue($m->fresh()->isOnline());
    }

    public function test_online_list_requires_function_and_shows_only_online(): void
    {
        $this->site('onlinemembersfunction');
        app(CurrentSite::class)->set('www');

        // 2文字の名前だと CSRF トークンや Livewire のチェックサム等、ページ内の
        // ランダム文字列と偶然一致して assertDontSee が不安定になるため、
        // 十分にユニークな表示名を使う。
        $online = $this->participant('u1', 'オンライン参加者テスト');
        $online->forceFill(['timerenew' => now()])->save();

        $stale = $this->participant('u2', 'オフライン参加者テスト');
        $stale->forceFill(['timerenew' => now()->subHour()])->save();

        $res = $this->actingAs($online)->get('/members/online')->assertOk();
        $res->assertSee('オンライン参加者テスト');
        $res->assertDontSee('オフライン参加者テスト');
    }

    public function test_online_list_404_without_function(): void
    {
        $this->site('memberlistfunction');
        $m = $this->participant('u1');

        $this->actingAs($m)->get('/members/online')->assertNotFound();
    }
}
