<?php
/**
 * 旧ASPサーバ上の実ファイル（files/{siteid}/WebUp/...）を S3/R2 に吸い上げ、
 * files.storage_key / size_bytes / mime を埋める一度きりのスクリプト。
 *
 * 実行: C:\xampp\php\php.exe schema-gen/migrate_legacy_files.php [site=www] [--dry]
 *
 * 旧ファイル名は id を前置した幾つかの形式が混在する:
 *   {id}.{ext} / {id}_{rand}.{ext} / {folder}/{id}_{rand}_{renban}.{ext} など。
 * DBの各行に対し WebUp 配下を「id 前置 + 拡張子一致」で探し、
 * foldername 一致 > サイズ最大 の順で1件選ぶ。
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\FileItem;
use App\Support\CurrentSite;
use App\Support\FileStorage;
use Illuminate\Support\Facades\Storage;

$site = $argv[1] ?? 'www';
$dry = in_array('--dry', $argv, true);
$webUp = "C:/inetpub/wwwroot/cto-asp/files/{$site}/WebUp";

if (! is_dir($webUp)) {
    fwrite(STDERR, "WebUp が見つかりません: {$webUp}\n");
    exit(1);
}

app(CurrentSite::class)->set($site);

// WebUp 配下の全ファイルを収集
$disk = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($webUp, FilesystemIterator::SKIP_DOTS));
$onDisk = [];
foreach ($disk as $f) {
    if ($f->isFile()) {
        $onDisk[] = [
            'path' => $f->getPathname(),
            'base' => $f->getFilename(),
            'dir' => basename($f->getPath()),
            'size' => $f->getSize(),
            'ext' => strtolower($f->getExtension()),
        ];
    }
}

$rows = FileItem::query()->withoutSiteScope()->where('site_id', $site)->whereNull('storage_key')->get();

$matched = $missing = $errors = 0;

foreach ($rows as $file) {
    $id = (int) $file->id;
    $ext = $file->fileext; // アクセサで trim/lower 済み

    $candidates = array_filter($onDisk, function ($d) use ($id, $ext) {
        // 「{id}」で始まり、直後が . か _ 、拡張子一致（jpg/jpeg は相互許容）
        if (! preg_match('/^'.$id.'(?=[._])/', $d['base'])) {
            return false;
        }
        $de = $d['ext'] === 'jpeg' ? 'jpg' : $d['ext'];
        $fe = $ext === 'jpeg' ? 'jpg' : $ext;

        return $de === $fe || $ext === '';
    });

    if (empty($candidates)) {
        echo "MISS  id={$id} .{$ext} {$file->filename}\n";
        $missing++;
        continue;
    }

    // foldername 一致 > "{id}_" 形式（新しい命名） > サイズ最大 の順で選ぶ
    $want = trim((string) $file->foldername);
    usort($candidates, function ($a, $b) use ($want, $id) {
        $am = $want !== '' && $a['dir'] === $want ? 1 : 0;
        $bm = $want !== '' && $b['dir'] === $want ? 1 : 0;
        if ($am !== $bm) return $bm <=> $am;

        $au = preg_match('/^'.$id.'_/', $a['base']) ? 1 : 0;
        $bu = preg_match('/^'.$id.'_/', $b['base']) ? 1 : 0;
        if ($au !== $bu) return $bu <=> $au;

        return $b['size'] <=> $a['size'];
    });
    $pick = $candidates[0];

    $key = FileStorage::keyFor($site, $ext ?: $pick['ext']);
    $mime = mime_content_type($pick['path']) ?: 'application/octet-stream';

    echo sprintf("MATCH id=%-4d %-28s -> %s  (%d bytes)\n", $id, $pick['base'], $key, $pick['size']);

    if ($dry) {
        $matched++;
        continue;
    }

    try {
        Storage::disk(FileStorage::DISK)->put($key, file_get_contents($pick['path']), ['ContentType' => $mime]);
        $file->storage_key = $key;
        $file->size_bytes = $pick['size'];
        $file->mime = $mime;
        $file->saveQuietly();
        $matched++;
    } catch (\Throwable $e) {
        echo "  ERROR: ".$e->getMessage()."\n";
        $errors++;
    }
}

echo "\n完了: matched={$matched} missing={$missing} errors={$errors} / 対象 ".$rows->count()." 行".($dry ? "（dry-run、書き込みなし）" : "")."\n";
