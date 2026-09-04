<?php

namespace App\Support;

use App\Models\Room;

/**
 * 旧ASPのページ名（`top_menus.linkaddress` / `content_sorts.link` 等に保存された
 * "index.asp" のような相対パスや "contents.asp?Contentsort=30" のようなクエリ付き
 * パス）を、新サイトのルートへできる範囲でマッピングする。
 *
 * 絶対URL（http/https）はそのまま外部リンクとして扱う。
 * 対応表に無い相対パスは $fallback（省略時は自サイトのトップ）を返す —
 * 旧データを保ったまま Filament 側で linkaddress / link を更新してもらう前提。
 */
class LegacyLinkResolver
{
    public static function resolve(?string $raw, ?Room $site, ?string $fallback = null): ?string
    {
        $raw = trim((string) $raw);

        if ($raw === '') {
            return $fallback;
        }

        if (preg_match('#^https?://#i', $raw)) {
            return $raw;
        }

        // クエリ文字列・アンカーを外してページ名だけで判定する
        $page = strtolower(explode('?', explode('#', $raw)[0])[0]);
        $page = ltrim($page, '/');

        return match (true) {
            $page === 'index.asp' => route('home'),
            in_array($page, ['otoi.asp', 'otoi2.asp', 'otoi3.asp'], true) => route('contact.create'),
            $page === 'faq.asp' => route('faq.index'),
            $page === 'news.asp' => route('news.index'),
            $page === 'contents.asp' => route('contents.index'),
            in_array($page, ['meetlist.asp', 'meet.asp'], true) && $site?->hasFunction('freeguestbookfunction') => route('board.index'),
            $page === 'managerwords.asp' && $site?->hasFunction('managerwordsfunction') => route('manager-words'),
            $page === 'friendlink.asp' && $site?->hasFunction('friendlinkfunction') => route('links.index'),
            default => $fallback,
        };
    }
}
