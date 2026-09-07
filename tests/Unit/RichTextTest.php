<?php

namespace Tests\Unit;

use App\Support\RichText;
use PHPUnit\Framework\TestCase;

class RichTextTest extends TestCase
{
    public function test_keeps_safe_formatting(): void
    {
        $in = '<div><strong>太字</strong> と <em>斜体</em></div>'
            .'<h1>見出し</h1><ul><li>項目</li></ul><blockquote>引用</blockquote>';

        $this->assertSame($in, RichText::clean($in));
    }

    public function test_strips_scripts_and_event_handlers(): void
    {
        $out = RichText::clean('こんにちは<script>alert(1)</script><img src=x onerror=alert(1)><div onclick="x()">c</div>');

        $this->assertStringNotContainsString('<script', (string) $out);
        $this->assertStringNotContainsString('onerror', (string) $out);
        $this->assertStringNotContainsString('onclick', (string) $out);
        $this->assertStringContainsString('こんにちは', (string) $out);
    }

    public function test_strips_dangerous_link_schemes_but_keeps_http(): void
    {
        $out = (string) RichText::clean(
            '<a href="https://example.com">ok</a> <a href="javascript:alert(1)">bad</a>'
        );

        $this->assertStringContainsString('href="https://example.com"', $out);
        $this->assertStringNotContainsString('javascript:', $out);
        $this->assertStringContainsString('bad', $out); // リンク文字は残る
    }

    public function test_empty_or_whitespace_only_becomes_null(): void
    {
        $this->assertNull(RichText::clean(null));
        $this->assertNull(RichText::clean('   '));
        $this->assertNull(RichText::clean('<div><br></div>'));
        $this->assertNull(RichText::clean('<p>&nbsp;</p>'));
    }

    public function test_to_plain_flattens_and_truncates(): void
    {
        $this->assertSame('太字 斜体', RichText::toPlain('<b>太字</b>  <i>斜体</i>'));
        $this->assertSame('abcde…', RichText::toPlain('<p>abcdefghij</p>', 5));
    }
}
