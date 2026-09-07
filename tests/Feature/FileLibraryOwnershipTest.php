<?php

namespace Tests\Feature;

use App\Models\FileItem;
use App\Models\Member;
use App\Models\MemberRoom;
use App\Models\Room;
use App\Support\FileStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * /files（会員ファイルライブラリ）は「自分がアップロードしたファイル」だけを見せる。
 * 他人のファイルは一覧にも出ず、ダウンロード/プレビューも 403（管理員は例外）。
 */
class FileLibraryOwnershipTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake(FileStorage::DISK);
    }

    private function setUpSite(): void
    {
        Room::create(['site_id' => 'www', 'sitename' => 'テスト', 'site_joutai' => 1, 'function_list' => 'filemanagefunction']);
        foreach (['alice' => 1, 'bob' => 1, 'boss' => -1] as $id => $ninshou) {
            Member::create(['member_id' => $id, 'name' => strtoupper($id)]);
            MemberRoom::create(['member_id' => $id, 'site_id' => 'www', 'ninshou' => $ninshou]);
        }
    }

    private function file(string $owner, string $name, bool $withBytes = false): FileItem
    {
        $key = $withBytes ? 'files/www/'.$name.'.txt' : null;
        if ($key) {
            Storage::disk(FileStorage::DISK)->put($key, 'x');
        }

        return FileItem::withoutGlobalScope('site')->create([
            'site_id' => 'www', 'member_id' => $owner, 'filename' => $name, 'fileext' => 'txt',
            'storage_key' => $key, 'size_bytes' => 1, 'adddt' => now(), 'renban' => 0,
        ]);
    }

    public function test_index_shows_only_the_current_users_files(): void
    {
        $this->setUpSite();
        $this->file('alice', 'alice-doc');
        $this->file('bob', 'bob-doc');

        $this->actingAs(Member::find('alice'))->get(route('files.index'))
            ->assertOk()
            ->assertSee('alice-doc')
            ->assertDontSee('bob-doc');
    }

    public function test_download_of_another_users_file_is_forbidden(): void
    {
        $this->setUpSite();
        $theirs = $this->file('bob', 'bob-doc', withBytes: true);

        $this->actingAs(Member::find('alice'))
            ->get(route('files.download', $theirs->id))
            ->assertForbidden();
    }

    public function test_owner_can_download_their_own_file(): void
    {
        $this->setUpSite();
        $mine = $this->file('alice', 'alice-doc', withBytes: true);

        $this->actingAs(Member::find('alice'))
            ->get(route('files.download', $mine->id))
            ->assertOk();
    }

    public function test_manager_can_reach_any_file(): void
    {
        $this->setUpSite();
        $aliceFile = $this->file('alice', 'alice-doc', withBytes: true);

        $this->actingAs(Member::find('boss'))
            ->get(route('files.download', $aliceFile->id))
            ->assertOk();
    }
}
