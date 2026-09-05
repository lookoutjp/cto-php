<?php

namespace App\Support;

/**
 * 管理者モード（フロント側のインライン編集導線）から Filament の作成/編集画面へ
 * `?back=<元のページURL>` を付けて遷移させ、保存後に元のページへ戻すために使う。
 *
 * オープンリダイレクト対策として、同一ホスト・http(s)スキームのURLのみ許可し、
 * それ以外（他ドメイン、javascript: など）は null を返して既定の遷移先に委ねる。
 */
class BackUrl
{
    public static function sanitize(?string $url): ?string
    {
        $url = trim((string) $url);

        if ($url === '') {
            return null;
        }

        $parsed = parse_url($url);

        if ($parsed === false || ! isset($parsed['path'])) {
            return null;
        }

        if (isset($parsed['scheme']) && ! in_array(strtolower($parsed['scheme']), ['http', 'https'], true)) {
            return null;
        }

        if (isset($parsed['host']) && $parsed['host'] !== request()->getHost()) {
            return null;
        }

        return $url;
    }
}
