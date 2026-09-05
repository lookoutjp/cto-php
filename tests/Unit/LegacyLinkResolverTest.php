<?php

namespace Tests\Unit;

use App\Models\Room;
use App\Support\LegacyLinkResolver;
use Tests\TestCase;

class LegacyLinkResolverTest extends TestCase
{
    public function test_absolute_urls_pass_through(): void
    {
        $this->assertSame('https://demo.cto.jp', LegacyLinkResolver::resolve('https://demo.cto.jp', null));
        $this->assertSame('http://example.com/x', LegacyLinkResolver::resolve('http://example.com/x', null));
        $this->assertSame('mailto:info@example.com', LegacyLinkResolver::resolve('mailto:info@example.com', null));
        $this->assertSame('tel:0312345678', LegacyLinkResolver::resolve('tel:0312345678', null));
    }

    public function test_known_legacy_pages_map_to_new_routes(): void
    {
        $this->assertSame(route('home'), LegacyLinkResolver::resolve('index.asp', null));
        $this->assertSame(route('contact.create'), LegacyLinkResolver::resolve('otoi.asp', null));
        $this->assertSame(route('faq.index'), LegacyLinkResolver::resolve('faq.asp', null));
    }

    /**
     * "contents.asp?Contentsort=30" は特定カテゴリへのリンクなので、
     * category クエリを引き継いだ上で新ルートに変換する（旧: 引き継がず /contents に落ちていた）。
     */
    public function test_contents_asp_with_category_query_preserves_the_category(): void
    {
        $this->assertSame(
            route('contents.index', ['category' => 30]),
            LegacyLinkResolver::resolve('contents.asp?Contentsort=30', null)
        );
        $this->assertSame(route('contents.index'), LegacyLinkResolver::resolve('contents.asp', null));
    }

    /**
     * 機能が無効なサイトでは meetlist.asp を掲示板とは解釈しない。
     * その場合も $fallback へ差し替えず、設定値をそのまま使う（＝掲示板機能が無いのに
     * 「掲示板」ボタンを置いていること自体が設定側の問題として気づける）。
     */
    public function test_function_gated_pages_require_the_function_enabled(): void
    {
        $withBoard = Room::make(['function_list' => 'freeguestbookfunction']);
        $withoutBoard = Room::make(['function_list' => '']);

        $this->assertSame(route('board.index'), LegacyLinkResolver::resolve('meetlist.asp', $withBoard, 'fallback'));
        $this->assertSame(url('/meetlist.asp'), LegacyLinkResolver::resolve('meetlist.asp', $withoutBoard, 'fallback'));
    }

    /**
     * 対応表に無いリンク先は、管理画面で設定された値をそのまま自サイトのパスとして使う
     * （$fallbackへ勝手に差し替えない）。存在しなければ404になるのが正しい挙動。
     */
    public function test_unmapped_relative_paths_are_used_literally_not_replaced_by_fallback(): void
    {
        $this->assertSame(url('/Global_Version.asp'), LegacyLinkResolver::resolve('Global_Version.asp', null, 'fallback'));
        $this->assertSame(url('/about-us'), LegacyLinkResolver::resolve('/about-us', null, 'fallback'));
    }

    public function test_blank_returns_fallback(): void
    {
        $this->assertSame('fallback', LegacyLinkResolver::resolve('', null, 'fallback'));
        $this->assertSame('fallback', LegacyLinkResolver::resolve(null, null, 'fallback'));
    }
}
