<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\FileItem;
use App\Models\FileTag;
use App\Models\Member;
use App\Models\Room;
use App\Support\CurrentSite;
use App\Support\FileStorage;
use App\Support\Plans;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * 会員ファイルライブラリ。旧ASP: filelist.asp / fileadd.asp / download.asp。
 * filemanagefunction が有効なサイトのプロジェクト参加者が利用できる。
 * 実体は S3/R2（disk 's3'）、ダウンロードはアプリ経由（テナント境界チェック後にストリーム）。
 */
class FileController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureEnabled();

        $tags = FileTag::query()->orderBy('tagname')->get(['tag_id', 'tagname']);

        $activeTag = $request->integer('tag') ?: null;

        $files = FileItem::query()
            ->listingOrder()
            ->when($activeTag, fn ($q) => $q->withTag($activeTag))
            ->with('uploader:member_id,name')
            ->paginate(20)
            ->withQueryString();

        $files->getCollection()->transform(function (FileItem $f) {
            $f->preview_url = $f->hasBytes() && FileStorage::isImage($f->fileext)
                ? Storage::disk(FileStorage::DISK)->temporaryUrl($f->storage_key, now()->addMinutes(30))
                : null;

            return $f;
        });

        $room = Room::find(app(CurrentSite::class)->id());

        return view('member.file-index', [
            'files' => $files,
            'tags' => $tags,
            'activeTag' => $activeTag,
            'storageUsed' => Plans::storageUsageBytes($room),
            'storageLimitMb' => $room->planLimit('storage_mb'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureEnabled();

        $tagIds = FileTag::query()->pluck('tag_id')->all();

        $data = $request->validate([
            'file' => [
                'required', 'file',
                'max:'.(FileStorage::MAX_BYTES / 1024), // KB
                'extensions:'.implode(',', FileStorage::ALLOWED_EXTENSIONS),
            ],
            'intro' => ['nullable', 'string', 'max:2000'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => [Rule::in($tagIds)],
        ], [], [
            'file' => 'ファイル', 'intro' => '説明', 'tag_ids' => 'タグ',
        ]);

        $upload = $data['file'];
        $room = Room::find(app(CurrentSite::class)->id());

        if (! Plans::withinStorageLimit($room, $upload->getSize())) {
            throw ValidationException::withMessages([
                'file' => 'プランのストレージ上限に達しています。プランのアップグレードまたは不要なファイルの削除をお願いします。',
            ]);
        }

        $ext = strtolower($upload->getClientOriginalExtension() ?: $upload->extension());
        $key = FileStorage::keyFor(app(CurrentSite::class)->id(), $ext);

        Storage::disk(FileStorage::DISK)->putFileAs('', $upload, $key, [
            'ContentType' => $upload->getMimeType(),
        ]);

        $originalName = pathinfo($upload->getClientOriginalName(), PATHINFO_FILENAME) ?: 'file';

        $file = new FileItem;
        $file->filename = $originalName;
        $file->fileext = $ext;
        $file->intro = $data['intro'] ?? null;
        $file->member_id = $request->user()->getKey();
        $file->tag_id = $this->packTags($data['tag_ids'] ?? []);
        $file->storage_key = $key;
        $file->size_bytes = $upload->getSize();
        $file->mime = $upload->getMimeType();
        $file->adddt = now();
        $file->renban = 0;
        $file->save(); // BelongsToSite が site_id をセット

        return redirect()->route('files.index')->with('status', 'ファイルをアップロードしました。');
    }

    public function download(Request $request, int $id): StreamedResponse
    {
        $file = $this->fileWithBytes($id);

        return Storage::disk(FileStorage::DISK)->download($file->storage_key, $file->downloadName());
    }

    /** ブラウザで inline 表示（PDF・画像・テキスト）。それ以外はダウンロード。 */
    public function preview(Request $request, int $id): StreamedResponse
    {
        $file = $this->fileWithBytes($id);

        if (! FileStorage::canPreviewInline($file->fileext)) {
            return Storage::disk(FileStorage::DISK)->download($file->storage_key, $file->downloadName());
        }

        return Storage::disk(FileStorage::DISK)->response($file->storage_key, $file->downloadName(), [
            'Content-Type' => $file->mime ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="'.addslashes($file->downloadName()).'"',
        ]);
    }

    private function fileWithBytes(int $id): FileItem
    {
        $this->ensureEnabled();

        $file = FileItem::query()->findOrFail($id); // BelongsToSite で他サイトは対象外

        if (! $file->hasBytes() || ! Storage::disk(FileStorage::DISK)->exists($file->storage_key)) {
            throw new NotFoundHttpException('このファイルの実体は保存されていません。');
        }

        return $file;
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $this->ensureEnabled();

        $file = FileItem::query()->findOrFail($id);

        abort_unless(
            $this->isManager($request) || (string) $file->member_id === (string) $request->user()->getKey(),
            403,
            'このファイルを削除する権限がありません。'
        );

        if ($file->hasBytes()) {
            Storage::disk(FileStorage::DISK)->delete($file->storage_key);
        }

        $file->delete();

        return redirect()->route('files.index')->with('status', 'ファイルを削除しました。');
    }

    /** tag_id の配列を旧ASP形式 ",6,7," に詰める。空なら null。 */
    private function packTags(array $ids): ?string
    {
        $ids = collect($ids)->map(fn ($v) => (int) $v)->filter()->unique()->values();

        return $ids->isEmpty() ? null : ','.$ids->implode(',').',';
    }

    private function isManager(Request $request): bool
    {
        $user = $request->user();

        return $user instanceof Member
            && ($user->isSuperAdmin() || $user->managesSite(app(CurrentSite::class)->id()));
    }

    private function ensureEnabled(): void
    {
        if (! Room::find(app(CurrentSite::class)->id())?->hasFunction('filemanagefunction')) {
            throw new NotFoundHttpException;
        }
    }
}
