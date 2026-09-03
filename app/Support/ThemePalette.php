<?php

namespace App\Support;

use App\Models\Room;

/**
 * サイト（テナント）ごとのテーマカラー。旧ASP: css/inc_Stytle.asp の
 * get_CssColor(sitecss, commoncolor3..0)。`rooms.sitecolor` の値で配色が決まる。
 *
 * 旧ASPの commoncolor3(濃)〜commoncolor0(淡) を、Tailwind から参照する
 * CSS カスタムプロパティ（--brand / --brand-dark / --brand-light / --brand-bg /
 * --brand-fg）に対応づける。--brand-name は `rooms.sitename_color`。
 */
class ThemePalette
{
    /** sitecolor => [c3(濃), c2, c1, c0(淡)]  ※旧ASP get_CssColor と同値 */
    private const MAP = [
        'red' => ['#ff0000', '#ff3838', '#ff6060', '#ff7a7a'],
        'css-pink' => ['#ff3dff', '#ff51ff', '#ff66ff', '#ff7aff'],
        'css-purple' => ['#b266ff', '#ce85ff', '#ce8fff', '#daa3ff'],
        'purple' => ['#ba5772', '#e36a8b', '#ff829f', '#ffb3c4'],
        'blue' => ['#007fff', '#4242ff', '#6060ff', '#7abcff'],
        'css-water' => ['#08e6ff', '#32e8ff', '#67eeff', '#9cf0ff'],
        'css-green' => ['#00e000', '#00f700', '#33ff40', '#7aff83'],
        'spring' => ['#008000', '#228b22', '#31b404', '#e0f8e0'],
        'yellow' => ['#ffd000', '#ffe042', '#ffff60', '#ffff7a'],
        'css-orange' => ['#ff7f00', '#ffa042', '#ffaf60', '#ffbc7a'],
        'gray' => ['#454545', '#707070', '#a7a7a7', '#efefe7'],
        'white' => ['#e6e6e6', '#e4e4e4', '#efefef', '#fafafa'],
        'gold' => ['#9a6b13', '#ba9b5d', '#e2d4bb', '#f2ece2'],
    ];

    /** 未設定 / 未知の sitecolor 用（現行デザインのニュートラルグレー） */
    private const FALLBACK = ['#374151', '#4b5563', '#9ca3af', '#f3f4f6'];

    public string $brand;

    public string $brandDark;

    public string $brandLight;

    public string $brandBg;

    public string $brandFg;

    public string $brandName;

    public function __construct(?string $sitecolor, ?string $sitenameColor = null)
    {
        [$c3, $c2, $c1, $c0] = self::MAP[strtolower((string) $sitecolor)] ?? self::FALLBACK;

        // c3 が明るすぎる（yellow/white 等）と実塗りボタンで文字が見えないため、
        // その場合だけ brand をスレート系に落とす（light/bg はテーマのまま）。
        if ($this->luminance($c3) > 0.7) {
            $this->brand = '#475569';
            $this->brandDark = '#334155';
        } else {
            $this->brand = $c3;
            $this->brandDark = $this->darken($c3, 0.85);
        }

        $this->brandLight = $c1;
        $this->brandBg = $c0;
        $this->brandFg = $this->luminance($this->brand) > 0.6 ? '#1f2937' : '#ffffff';
        $this->brandName = $this->normalizeHex($sitenameColor) ?? $this->brand;
    }

    public static function forSite(?Room $site): self
    {
        return new self($site?->sitecolor, $site?->sitename_color);
    }

    /** <style> に流し込む CSS カスタムプロパティ宣言。 */
    public function cssVars(): string
    {
        return implode('', [
            "--brand:{$this->brand};",
            "--brand-dark:{$this->brandDark};",
            "--brand-light:{$this->brandLight};",
            "--brand-bg:{$this->brandBg};",
            "--brand-fg:{$this->brandFg};",
            "--brand-name:{$this->brandName};",
        ]);
    }

    private function normalizeHex(?string $v): ?string
    {
        $v = trim((string) $v);

        return preg_match('/^#?[0-9a-fA-F]{6}$/', $v) ? (str_starts_with($v, '#') ? $v : "#$v") : null;
    }

    /** @return array{0:int,1:int,2:int} */
    private function rgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    }

    /** 相対輝度（0=黒, 1=白）ざっくり版。 */
    private function luminance(string $hex): float
    {
        [$r, $g, $b] = $this->rgb($hex);

        return (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;
    }

    private function darken(string $hex, float $factor): string
    {
        [$r, $g, $b] = $this->rgb($hex);

        return sprintf('#%02x%02x%02x', (int) ($r * $factor), (int) ($g * $factor), (int) ($b * $factor));
    }
}
