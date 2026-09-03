<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Content;
use App\Models\ContentSort;
use App\Models\Member;
use App\Support\FileStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * 添付ファイルのダウンロード（アプリ経由でストリーム）。
 *
 * コンテンツの添付: そのコンテンツが公開なら誰でも / 非公開ならプロジェクト参加者。
 * それ以外（WBS・タスク）: 現在サイトのプロジェクト参加者のみ。
 */
class AttachmentController extends Controller
{
    public function download(Request $request, int $id): StreamedResponse
    {
        $attachment = $this->find($request, $id);

        return Storage::disk(FileStorage::DISK)->download($attachment->storage_key, $attachment->downloadName());
    }

    /** ブラウザで inline 表示（PDF・画像・テキスト）。それ以外はダウンロードにフォールバック。 */
    public function preview(Request $request, int $id): StreamedResponse
    {
        $attachment = $this->find($request, $id);

        if (! $attachment->canPreviewInline()) {
            return Storage::disk(FileStorage::DISK)->download($attachment->storage_key, $attachment->downloadName());
        }

        return Storage::disk(FileStorage::DISK)->response($attachment->storage_key, $attachment->downloadName(), [
            'Content-Type' => $attachment->mime ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="'.addslashes($attachment->downloadName()).'"',
        ]);
    }

    private function find(Request $request, int $id): Attachment
    {
        $attachment = Attachment::query()->with('attachable')->findOrFail($id); // BelongsToSite で他サイトは除外

        $this->authorizeView($request, $attachment);

        if (! Storage::disk(FileStorage::DISK)->exists($attachment->storage_key)) {
            throw new NotFoundHttpException('このファイルは見つかりませんでした。');
        }

        return $attachment;
    }

    private function authorizeView(Request $request, Attachment $attachment): void
    {
        $subject = $attachment->attachable;

        // 添付先が消えている孤児は見せない
        if ($subject === null) {
            throw new NotFoundHttpException;
        }

        $user = $request->user();
        $isProjectMember = $user instanceof Member && $user->isProjectMemberOf();

        if ($subject instanceof Content) {
            $publiclyVisible = (int) $subject->ok === 1
                && ContentSort::query()->publicVisible()->whereKey($subject->content_sort)->exists();

            abort_unless($publiclyVisible || $isProjectMember, 403);

            return;
        }

        abort_unless($isProjectMember, 403);
    }
}
