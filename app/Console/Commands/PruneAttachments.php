<?php

namespace App\Console\Commands;

use App\Models\Attachment;
use App\Support\FileStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * 添付先が消えている / 論理削除されている孤児 attachment を掃除する。
 *   php artisan attachments:prune [--dry]
 */
class PruneAttachments extends Command
{
    protected $signature = 'attachments:prune {--dry : 削除せず対象だけ表示}';

    protected $description = '添付先が無い / 論理削除された孤児の添付ファイルを削除する';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry');
        $pruned = 0;

        Attachment::query()->withoutGlobalScope('site')->with('attachable')->chunkById(200, function ($rows) use ($dry, &$pruned) {
            foreach ($rows as $attachment) {
                $subject = $attachment->attachable;

                $orphan = $subject === null
                    || (isset($subject->delete_to) && (int) $subject->delete_to === 1);

                if (! $orphan) {
                    continue;
                }

                $this->line(sprintf(
                    '%s #%d  %s (%s)',
                    class_basename($attachment->attachable_type),
                    $attachment->attachable_id,
                    $attachment->original_name,
                    $attachment->storage_key,
                ));

                if (! $dry) {
                    if (filled($attachment->storage_key)) {
                        Storage::disk(FileStorage::DISK)->delete($attachment->storage_key);
                    }
                    $attachment->delete();
                }

                $pruned++;
            }
        });

        $this->info(($dry ? '対象' : '削除').": {$pruned} 件".($dry ? '（dry-run）' : ''));

        return self::SUCCESS;
    }
}
