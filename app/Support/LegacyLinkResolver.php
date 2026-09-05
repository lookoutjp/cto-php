<?php

namespace App\Support;

use App\Models\Room;

/**
 * top_menus.linkaddress / content_sorts.link に設定された「リンク先」を実際のURLに変換する。
 *
 * 優先順位:
 *   1. 空文字                  → $fallback（何も設定されていない場合の既定動作）
 *   2. http(s)/mailto/tel の絶対URL → そのまま使う
 *   3. 旧ASPの定番ページ名（index.asp 等）→ 新システムの対応ルートに変換
 *      （contents.asp?Contentsort=N は category=N を引き継ぐ）
 *   4. それ以外                → 管理画面に設定された値をそのまま自サイトのパスとして使う
 *
 * 3.までは「管理員が意図した遷移先」を新システムの実際のルートに読み替えているだけで、
 * 設定を無視しているわけではない。4.が肝心で、対応表に無い値を勝手に別の場所（トップ
 * ページ等）へ差し替えることはしない — 存在しないパスなら404になるが、それは管理画面の
 * 「リンク先」を直すべきというサインであり、黙って別ページに飛ばすより正しい挙動。
 */
class LegacyLinkResolver
{
    public static function resolve(?string $raw, ?Room $site, ?string $fallback = null): ?string
    {
        $raw = trim((string) $raw);

        if ($raw === '') {
            return $fallback;
        }

        if (preg_match('#^(https?|mailto|tel):#i', $raw)) {
            return $raw;
        }

        [$path, $query] = self::splitPathAndQuery($raw);
        $page = strtolower(ltrim($path, '/'));

        $mapped = match (true) {
            $page === 'index.asp' => route('home'),
            $page === 'aboutsite.asp' => route('about'),
            in_array($page, ['otoi.asp', 'otoi2.asp', 'otoi3.asp'], true) => route('contact.create'),
            $page === 'faq.asp' => route('faq.index'),
            $page === 'news.asp' => route('news.index'),
            $page === 'contents.asp' => self::contentsUrl($query),
            in_array($page, ['meetlist.asp', 'meet.asp'], true) && $site?->hasFunction('freeguestbookfunction') => route('board.index'),
            $page === 'managerwords.asp' && $site?->hasFunction('managerwordsfunction') => route('manager-words'),
            $page === 'friendlink.asp' && $site?->hasFunction('friendlinkfunction') => route('links.index'),
            default => null,
        };

        return $mapped ?? url('/'.ltrim($raw, '/'));
    }

    /**
     * "contents.asp?Contentsort=30#foo" のようなクエリ・アンカー付きパスを分解する。
     *
     * @return array{0: string, 1: array<string, string>}
     */
    private static function splitPathAndQuery(string $raw): array
    {
        [$withoutAnchor] = explode('#', $raw, 2);
        $parts = explode('?', $withoutAnchor, 2);

        parse_str($parts[1] ?? '', $query);

        return [$parts[0], $query];
    }

    /** @param array<string, string> $query */
    private static function contentsUrl(array $query): string
    {
        $categoryId = $query['Contentsort'] ?? $query['contentsort'] ?? null;

        return filled($categoryId)
            ? route('contents.index', ['category' => (int) $categoryId])
            : route('contents.index');
    }
}
