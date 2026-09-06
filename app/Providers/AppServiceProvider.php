<?php

namespace App\Providers;

use App\Auth\LegacyAwareUserProvider;
use App\Auth\Passwords\CustomPasswordBrokerManager;
use App\Models\ContentSort;
use App\Models\Room;
use App\Models\TopMenu;
use App\Support\AdminMode;
use App\Support\CurrentSite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // 現在のサイト(テナント)を表すシングルトン。BelongsToSite トレイトが参照する。
        $this->app->singleton(CurrentSite::class);

        // 旧Access由来のpassword_reset_tokensテーブル構成に対応するため、
        // 標準のPasswordBrokerManagerをCustomPasswordBrokerManagerに差し替える。
        // 'auth.password' は PasswordResetServiceProvider が DeferredProvider として
        // 遅延登録するため、単純な singleton() 上書きでは実際の解決時に上書き返されてしまう。
        // extend() を使うことで、遅延登録が走った後でも確実に差し替わるようにする。
        $this->app->extend('auth.password', function ($manager, $app) {
            return new CustomPasswordBrokerManager($app);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 課金の単位はテナント。Cashier の顧客モデルを users から Room に変更する。
        Cashier::useCustomerModel(Room::class);

        // 旧ASP由来の非bcryptパスワードでもログインでき、成功時にbcryptへ移行する
        // UserProvider。config/auth.php の providers.users.driver で参照。
        Auth::provider('legacy-aware-eloquent', function ($app, array $config) {
            return new LegacyAwareUserProvider($app['hash'], $config['model']);
        });

        // 現在のサイト情報($site)を、公開フロント・会員画面・共通レイアウトのビューに渡す。
        View::composer([
            'components.layouts.public', 'layouts.app', 'layouts.navigation', 'layouts.guest',
            'public.*', 'livewire.public.*', 'auth.*',
            'member.*', 'livewire.member.*', 'mypage',
        ], function ($view) {
            $view->with('site', once(fn () => Room::find(app(CurrentSite::class)->id())));
        });

        // 公開フロントの共通ヘッダー用トップメニュー（旧 inc_top.asp のボタン列、top_menus）と
        // 全ページ共通の左サイドバー「カテゴリ」（旧 inc_left.asp、content_sorts のトップレベル）。
        View::composer('components.layouts.public', function ($view) {
            $view->with('topMenus', once(fn () => TopMenu::query()->orderBy('junban')->orderBy('id')->get()));
            $view->with('sidebarCategories', once(fn () => ContentSort::query()->publicVisible()->topLevel()->listingOrder()->get()));
        });

        // 旧ASPの「管理員モード」相当。サイト管理員がONにしている間だけ、公開フロントの
        // 各画面に「追加/編集/削除（→ Filament）」の導線を出す。共通レイアウトだけでなく
        // 個々のページビュー（public.* / livewire.public.*）でも $adminMode を使えるようにする。
        // session 由来で1リクエスト中でも変わり得るため once() ではキャッシュしない。
        View::composer(
            ['components.layouts.public', 'public.*', 'livewire.public.*'],
            function ($view) {
                $view->with('adminMode', AdminMode::activeFor(app(CurrentSite::class)->idOrNull()));
            }
        );
    }
}
