<?php

namespace App\Models\Concerns;

use App\Models\Attachment;
use App\Support\FileStorage;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;

/**
 * コンテンツ / WBS / タスクに添付ファイルを持たせる。実体は S3/R2。
 * モデルが（物理）削除されたら添付レコードと R2 オブジェクトも消す。
 */
trait HasAttachments
{
    public static function bootHasAttachments(): void
    {
        static::deleting(function ($model) {
            foreach ($model->attachments()->get() as $attachment) {
                if (filled($attachment->storage_key)) {
                    Storage::disk(FileStorage::DISK)->delete($attachment->storage_key);
                }
                $attachment->delete();
            }
        });
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable')->orderBy('created_at')->orderBy('id');
    }
}
