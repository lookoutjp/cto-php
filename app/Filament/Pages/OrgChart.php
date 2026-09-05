<?php

namespace App\Filament\Pages;

use App\Models\Level;
use App\Models\Member;
use App\Support\CurrentSite;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

/**
 * 現在サイトの組織図（旧 orgchart.asp「体制図」）。
 * `levels`（旧 lebel）の `fatherlevel` 自己参照ツリーを表示する。
 * 編集は LevelResource（`/admin/levels`）側。
 */
class OrgChart extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-share';

    protected static ?int $navigationSort = 345;

    protected static string $view = 'filament.pages.org-chart';

    public static function getNavigationLabel(): string
    {
        return '組織図';
    }

    public function getTitle(): string|Htmlable
    {
        return '組織図';
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        $siteId = app(CurrentSite::class)->idOrNull();

        return $user instanceof Member
            && $siteId !== null
            && $user->managesSite($siteId);
    }

    protected function getViewData(): array
    {
        return [
            'roots' => Level::tree(),
            'total' => Level::query()->count(),
        ];
    }
}
