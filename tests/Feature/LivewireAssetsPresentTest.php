<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\MemberRoom;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * layouts.app / layouts.guest は Livewire コンポーネントを埋め込まないページ
 * （マイページ・ログイン等）でも使われる。@livewireStyles/@livewireScripts が
 * 無いと、そのページには Livewire 同梱の Alpine が一切読み込まれず、
 * x-data系のUI（設定ドロップダウン・ハンバーガー・MyMenuの折りたたみ等）が
 * 無反応になる（実際にこれで壊れていた）。退行防止のため生成HTMLに
 * livewire のスクリプトタグが含まれることを確認する。
 */
class LivewireAssetsPresentTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_layout_includes_livewire_assets_without_any_livewire_component(): void
    {
        Room::create(['site_id' => 'www', 'sitename' => 'テスト', 'site_joutai' => 1, 'function_list' => '']);
        $member = Member::create(['member_id' => 'm1', 'name' => 'テスト']);
        MemberRoom::create(['member_id' => 'm1', 'site_id' => 'www', 'ninshou' => 1]);

        // mypage.blade.php は <livewire:...> を一切埋め込まない静的ページ
        $response = $this->actingAs($member)->get('/mypage')->assertOk();

        $response->assertSee('livewire.js', false);
    }

    public function test_guest_layout_includes_livewire_assets(): void
    {
        $this->get('/login')->assertOk()->assertSee('livewire.js', false);
    }
}
