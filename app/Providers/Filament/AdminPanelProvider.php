<?php

namespace App\Providers\Filament;

use App\Http\Middleware\ResolveCurrentSite;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            // ロゴをクリックすると公開フロントのトップページへ戻る（旧ASPのロゴ動作に合わせる）
            ->homeUrl(fn () => route('home'))
            ->brandLogo(fn () => new HtmlString(view('filament.partials.brand-logo')->render()))
            ->brandLogoHeight('2rem')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            // 左メニューをトップバーのボタンで非表示/表示に切り替えられるように（デスクトップで完全に隠せる）。開閉状態はブラウザに記憶される。
            ->sidebarFullyCollapsibleOnDesktop()
            ->renderHook(
                PanelsRenderHook::TOPBAR_START,
                fn (): string => Blade::render('@livewire(\'site-switcher\')'),
            )
            // 一覧のタイトル行 + 左端N列を固定表示にするページ（route名 => 固定する列数）。
            // 列数は「チェックボックス列 + 操作ボタン列 + 指定の列まで」の合計。
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                function (): string {
                    $stickyTableFrozenColsByRoute = [
                        'filament.admin.resources.members.index' => 3, // 会員: 編集 + メールアドレス
                        'filament.admin.resources.member-rooms.index' => 5, // 会員権限: 承認/却下/編集 + サイトID + メールアドレス + 会員
                    ];

                    foreach ($stickyTableFrozenColsByRoute as $routeName => $frozenCols) {
                        if (request()->routeIs($routeName)) {
                            return view('filament.partials.sticky-frozen-table', ['frozenCols' => $frozenCols])->render();
                        }
                    }

                    return '';
                },
            )
            ->userMenuItems([
                'mypage' => MenuItem::make()
                    ->label('マイページへ戻る')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->url(fn () => route('dashboard')),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                ResolveCurrentSite::class,
            ]);
    }
}
