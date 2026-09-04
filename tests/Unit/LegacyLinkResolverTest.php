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
    }

    public function test_known_legacy_pages_map_to_new_routes(): void
    {
        $this->assertSame(route('home'), LegacyLinkResolver::resolve('index.asp', null));
        $this->assertSame(route('contact.create'), LegacyLinkResolver::resolve('otoi.asp', null));
        $this->assertSame(route('faq.index'), LegacyLinkResolver::resolve('faq.asp', null));
        $this->assertSame(route('contents.index'), LegacyLinkResolver::resolve('contents.asp?Contentsort=30', null));
    }

    public function test_function_gated_pages_require_the_function_enabled(): void
    {
        $withBoard = Room::make(['function_list' => 'freeguestbookfunction']);
        $withoutBoard = Room::make(['function_list' => '']);

        $this->assertSame(route('board.index'), LegacyLinkResolver::resolve('meetlist.asp', $withBoard, 'fallback'));
        $this->assertSame('fallback', LegacyLinkResolver::resolve('meetlist.asp', $withoutBoard, 'fallback'));
    }

    public function test_unknown_relative_paths_use_fallback(): void
    {
        $this->assertSame('fallback', LegacyLinkResolver::resolve('Global_Version.asp', null, 'fallback'));
        $this->assertNull(LegacyLinkResolver::resolve('Global_Version.asp', null));
    }

    public function test_blank_returns_fallback(): void
    {
        $this->assertSame('fallback', LegacyLinkResolver::resolve('', null, 'fallback'));
        $this->assertSame('fallback', LegacyLinkResolver::resolve(null, null, 'fallback'));
    }
}
