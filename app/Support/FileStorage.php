<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * 会員ファイルライブラリ（旧 filelist.asp / fileadd.asp）の保存規約。
 * 実体は S3/R2（disk 's3'）に `sites/{site_id}/files/{uuid}.{ext}` で置く。
 */
class FileStorage
{
    public const DISK = 's3';

    /** アップロード可能な拡張子（旧ASP fileadd_save.asp の Forum_upload 相当）。 */
    public const ALLOWED_EXTENSIONS = [
        'gif', 'jpg', 'jpeg', 'jpe', 'bmp', 'png', 'webp', 'svg',
        'pdf', 'txt', 'csv',
        'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
        'zip', 'rar', 'lzh', '7z',
        'mp3', 'mp4', 'mid',
    ];

    /** 1ファイルの上限バイト数。 */
    public const MAX_BYTES = 25 * 1024 * 1024;

    /** 画像プレビュー対象の拡張子。 */
    public const IMAGE_EXTENSIONS = ['gif', 'jpg', 'jpeg', 'jpe', 'bmp', 'png', 'webp'];

    /** ブラウザで inline 表示できる拡張子（画像＋PDF＋テキスト）。 */
    public const INLINE_EXTENSIONS = ['gif', 'jpg', 'jpeg', 'jpe', 'bmp', 'png', 'webp', 'svg', 'pdf', 'txt', 'csv'];

    public static function keyFor(string $siteId, string $ext): string
    {
        $ext = strtolower(ltrim($ext, '.'));
        $siteId = preg_replace('/[^a-zA-Z0-9_-]/', '', $siteId) ?: 'unknown';

        return "sites/{$siteId}/files/".Str::uuid()->toString().($ext !== '' ? ".{$ext}" : '');
    }

    public static function isImage(?string $ext): bool
    {
        return in_array(strtolower((string) $ext), self::IMAGE_EXTENSIONS, true);
    }

    public static function isPdf(?string $ext): bool
    {
        return strtolower((string) $ext) === 'pdf';
    }

    /** ブラウザで別タブ表示できる（ダウンロードを強制しない）か。 */
    public static function canPreviewInline(?string $ext): bool
    {
        return in_array(strtolower((string) $ext), self::INLINE_EXTENSIONS, true);
    }

    public static function humanSize(?int $bytes): string
    {
        $bytes = (int) $bytes;
        if ($bytes <= 0) {
            return '—';
        }
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = (int) floor(log($bytes, 1024));
        $i = min($i, count($units) - 1);

        return round($bytes / (1024 ** $i), $i === 0 ? 0 : 1).' '.$units[$i];
    }
}
