<?php

namespace Tests\Feature;

use App\Filament\Resources\ContentSortManagerResource;
use App\Filament\Resources\ContentSortManagerResource\Pages\ListContentSortManagers;
use App\Models\ContentSort;
use App\Models\ContentSortManager;
use App\Models\Member;
use App\Models\MemberRoom;
use App\Models\Room;
use App\Support\CurrentSite;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryManagerTest extends TestCase
{
    use RefreshDatabase;

    private ContentSort $root;

    private ContentSort $child;

    private ContentSort $grandchild;

    protected function setUp(): void
    {
        parent::setUp();
        Room::create(['site_id' => 'www', 'sitename' => 'テスト', 'site_joutai' => 1, 'function_list' => '']);
        app(CurrentSite::class)->set('www');

        $this->root = ContentSort::create(['site_id' => 'www', 'name' => 'ルート寄り', 'father_id' => 0, 'junban' => 1]);
        $this->child = ContentSort::create(['site_id' => 'www', 'name' => '子', 'father_id' => $this->root->id, 'junban' => 1]);
        $this->grandchild = ContentSort::create(['site_id' => 'www', 'name' => '孫', 'father_id' => $this->child->id, 'junban' => 1]);
    }

    private function member(string $id, int $ninshou = 0): Member
    {
        $m = Member::create(['member_id' => $id, 'name' => strtoupper($id)]);
        MemberRoom::create(['member_id' => $id, 'site_id' => 'www', 'ninshou' => $ninshou]);

        return $m;
    }

    public function test_manages_category_covers_the_whole_subtree(): void
    {
        $member = $this->member('mgr');
        ContentSortManager::create([
            'content_sort_id' => $this->child->id, 'member_id' => 'mgr',
            'site_id' => 'www', 'status' => 'approved', 'decided_at' => now(),
        ]);
        $member->refresh();

        $this->assertTrue($member->managesCategory($this->child));
        $this->assertTrue($member->managesCategory($this->grandchild)); // 子孫
        $this->assertFalse($member->managesCategory($this->root));       // 祖先は管理しない
    }

    public function test_site_admin_and_super_admin_manage_all_categories(): void
    {
        $siteAdmin = $this->member('admin', ninshou: -1);
        $this->assertTrue($siteAdmin->managesCategory($this->root));
        $this->assertTrue($siteAdmin->managesCategory($this->grandchild));

        config(['app.super_admin_member_ids' => ['super']]);
        $super = Member::create(['member_id' => 'super', 'name' => 'S']);
        $this->assertTrue($super->managesCategory($this->root));
    }

    public function test_pending_application_does_not_grant_management(): void
    {
        $member = $this->member('applicant');

        $this->actingAs($member)->post(route('contents.apply-manager', $this->child->id))->assertRedirect();

        $this->assertDatabaseHas('content_sort_managers', [
            'content_sort_id' => $this->child->id, 'member_id' => 'applicant', 'status' => 'pending',
        ]);
        $this->assertFalse($member->fresh()->managesCategory($this->child));
    }

    public function test_super_admin_approves_application_via_filament(): void
    {
        config(['app.super_admin_member_ids' => ['super']]);
        $super = Member::create(['member_id' => 'super', 'name' => 'S']);
        MemberRoom::create(['member_id' => 'super', 'site_id' => 'www', 'ninshou' => -1]);

        $applicant = $this->member('applicant');
        $app = ContentSortManager::create([
            'content_sort_id' => $this->child->id, 'member_id' => 'applicant',
            'site_id' => 'www', 'status' => 'pending', 'applied_at' => now(),
        ]);

        $this->actingAs($super);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(ListContentSortManagers::class)
            ->assertCanSeeTableRecords([$app])
            ->callTableAction('approve', $app);

        $app->refresh();
        $this->assertSame('approved', $app->status);
        $this->assertSame('super', $app->decided_by);
        $this->assertTrue($applicant->fresh()->managesCategory($this->child));
    }

    public function test_non_super_admin_cannot_access_the_resource(): void
    {
        $siteAdmin = $this->member('admin', ninshou: -1);
        $this->actingAs($siteAdmin);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->assertFalse(ContentSortManagerResource::canAccess());
    }
}
