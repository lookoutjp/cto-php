<?php

namespace App\Support;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * 会員が投稿するリッチテキスト（掲示板の本文・返信、メッセージ本文）の HTML を
 * 許可リスト方式でサニタイズする。エディタは Trix（resources/js）。
 *
 * 保存前に必ず RichText::clean() を通すこと。表示側は許可済み HTML を
 * `.trix-content` コンテナ内で {!! !!} 出力する。
 */
class RichText
{
    /** Trix が出力し得る、かつ表示して安全なタグだけを許可する。 */
    private const ALLOWED_TAGS = [
        'p', 'br', 'div', 'span',
        'strong', 'b', 'em', 'i', 'u', 's', 'del', 'ins', 'sub', 'sup', 'mark',
        'h1', 'h2', 'h3', 'h4',
        'ul', 'ol', 'li',
        'blockquote', 'pre', 'code',
        'a',
        'hr',
    ];

    public static function clean(?string $html): ?string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return null;
        }

        $config = new HtmlSanitizerConfig;

        foreach (self::ALLOWED_TAGS as $tag) {
            $config = $config->allowElement($tag, $tag === 'a' ? ['href'] : []);
        }

        $config = $config
            // http/https/mailto のみ。javascript: 等はブロック。
            ->allowLinkSchemes(['https', 'http', 'mailto'])
            ->dropElement('script')
            ->dropElement('style')
            ->dropElement('iframe')
            ->dropElement('form')
            ->withMaxInputLength(200_000);

        $clean = trim((new HtmlSanitizer($config))->sanitize($html));

        // 実体がタグだけ（空 <div><br></div> など）なら null に寄せる。
        $stripped = trim(preg_replace('/\x{00a0}|&nbsp;/u', '', strip_tags($clean)) ?? '');

        return $stripped === '' ? null : $clean;
    }

    /** 一覧のプレビュー等でプレーンテキストにする。 */
    public static function toPlain(?string $html, int $limit = 120): string
    {
        $text = trim(html_entity_decode(strip_tags((string) $html)));
        $text = preg_replace('/\s+/u', ' ', $text) ?? '';

        return mb_strlen($text) > $limit ? mb_substr($text, 0, $limit).'…' : $text;
    }
}
