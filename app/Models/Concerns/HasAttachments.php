<?php

namespace App\Models\Concerns;

use App\Models\Attachment;
use App\Support\FileStorage;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;

/**
 * コンテンツ / WBS / タスクに添付ファイルを持たせる。実体は S3/R2。
 *
 * 物理削除（`delete()`）: 添付レコード＋R2オブジェクトも消す。
 * 論理削除（`delete_to` を 1 に更新）: 同上（タスク/WBSはこちら）。
 */
trait HasAttachments
{
    public static function bootHasAttachments(): void
    {
        static::deleting(function ($model) {
            $model->purgeAttachments();
        });

        static::updated(function ($model) {
            if ($model->wasChanged('delete_to') && (int) $model->delete_to === 1) {
                $model->purgeAttachments();
            }
        });
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable')->orderBy('created_at')->orderBy('id');
    }

    /** この添付をすべて削除（レコード + R2 オブジェクト）。 */
    public function purgeAttachments(): void
    {
        foreach ($this->attachments()->get() as $attachment) {
            if (filled($attachment->storage_key)) {
                Storage::disk(FileStorage::DISK)->delete($attachment->storage_key);
            }
            $attachment->delete();
        }
    }
}
