<?php

namespace App\Livewire;

use App\Models\Attachment;
use App\Models\Content;
use App\Models\Member;
use App\Models\Room;
use App\Support\Attachables;
use App\Support\CurrentSite;
use App\Support\FileStorage;
use App\Support\Plans;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * コンテンツ / WBS / タスク詳細に埋め込む添付ファイルパネル（旧ASPには無い新機能）。
 *
 *   <livewire:attachments-panel type="wbs" :id="$node->id" :key="'att-wbs-'.$node->id" />
 *
 * 閲覧: コンテンツの添付は公開コンテンツなら誰でも／それ以外はプロジェクト参加者。
 * 追加・削除: プロジェクト参加者（削除はアップロード者本人 or サイト管理員）。
 */
class AttachmentsPanel extends Component
{
    use WithFileUploads;

    public string $type;

    public int $id;

    public $file = null;

    public function mount(string $type, int $id): void
    {
        abort_unless(Attachables::classFor($type) !== null, 404);
        $this->type = $type;
        $this->id = $id;
    }

    #[Computed]
    public function subject()
    {
        return Attachables::resolve($this->type, $this->id);
    }

    #[Computed]
    public function canView(): bool
    {
        $subject = $this->subject;
        if ($subject === null) {
            return false;
        }

        $user = auth()->user();
        $isMember = $user instanceof Member && $user->isProjectMemberOf();

        if ($subject instanceof Content) {
            return $isMember || ((int) $subject->ok === 1);
        }

        return $isMember;
    }

    #[Computed]
    public function canEdit(): bool
    {
        $user = auth()->user();

        return $this->subject !== null
            && $user instanceof Member
            && $user->isProjectMemberOf();
    }

    public function isManager(): bool
    {
        $user = auth()->user();

        return $user instanceof Member
            && ($user->isSuperAdmin() || $user->managesSite(app(CurrentSite::class)->id()));
    }

    /** その添付を削除できるか（アップロード者本人 or サイト管理員）。 */
    public function canRemove(?string $ownerId): bool
    {
        return $this->canEdit()
            && ($this->isManager() || (string) $ownerId === (string) auth()->id());
    }

    public function save(): void
    {
        abort_unless($this->canEdit(), 403);

        $this->validate([
            'file' => [
                'required', 'file',
                'max:'.(FileStorage::MAX_BYTES / 1024),
                'extensions:'.implode(',', FileStorage::ALLOWED_EXTENSIONS),
            ],
        ], [], ['file' => 'ファイル']);

        $room = Room::find(app(CurrentSite::class)->id());
        if (! Plans::withinStorageLimit($room, (int) $this->file->getSize())) {
            throw ValidationException::withMessages([
                'file' => 'プランのストレージ上限に達しています。',
            ]);
        }

        $ext = strtolower($this->file->getClientOriginalExtension() ?: $this->file->extension());
        $key = 'sites/'.app(CurrentSite::class)->id().'/attachments/'.Str::uuid()->toString().($ext ? ".{$ext}" : '');

        Storage::disk(FileStorage::DISK)->putFileAs('', $this->file, $key, [
            'ContentType' => $this->file->getMimeType(),
        ]);

        $attachment = new Attachment;
        $attachment->attachable_type = $this->subject::class;
        $attachment->attachable_id = $this->id;
        $attachment->storage_key = $key;
        $attachment->original_name = $this->file->getClientOriginalName();
        $attachment->ext = $ext ?: null;
        $attachment->size_bytes = $this->file->getSize();
        $attachment->mime = $this->file->getMimeType();
        $attachment->member_id = auth()->id();
        $attachment->created_at = now();
        $attachment->save(); // BelongsToSite が site_id をセット

        $this->reset('file');
        session()->flash('att-status', 'ファイルを添付しました。');
    }

    public function remove(int $attachmentId): void
    {
        $attachment = Attachment::query()
            ->where('attachable_type', $this->subject::class)
            ->where('attachable_id', $this->id)
            ->findOrFail($attachmentId);

        abort_unless(
            $this->isManager() || (string) $attachment->member_id === (string) auth()->id(),
            403,
        );

        Storage::disk(FileStorage::DISK)->delete($attachment->storage_key);
        $attachment->delete();

        session()->flash('att-status', '添付を削除しました。');
    }

    public function render(): View
    {
        if (! $this->canView()) {
            return view('livewire.attachments-panel-hidden');
        }

        $attachments = $this->subject
            ->attachments()
            ->with('uploader:member_id,name')
            ->get()
            ->map(function (Attachment $a) {
                $a->preview_url = $a->isImage()
                    ? Storage::disk(FileStorage::DISK)->temporaryUrl($a->storage_key, now()->addMinutes(30))
                    : null;

                return $a;
            });

        return view('livewire.attachments-panel', compact('attachments'));
    }
}
