<?php

namespace App\Providers;

use App\Auth\LegacyAwareUserProvider;
use App\Auth\Passwords\CustomPasswordBrokerManager;
use App\Models\Room;
use App\Support\CurrentSite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        // 旧ASP由来の非bcryptパスワードでもログインでき、成功時にbcryptへ移行する
        // UserProvider。config/auth.php の providers.users.driver で参照。
        Auth::provider('legacy-aware-eloquent', function ($app, array $config) {
            return new LegacyAwareUserProvider($app['hash'], $config['model']);
        });

        // 現在のサイト情報($site)を、公開フロント・会員画面・共通レイアウトのビューに渡す。
        View::composer([
            'components.layouts.public', 'layouts.app', 'layouts.navigation', 'layouts.guest',
            'public.*', 'livewire.public.*',
            'member.*', 'livewire.member.*', 'mypage',
        ], function ($view) {
            $view->with('site', once(fn () => Room::find(app(CurrentSite::class)->id())));
        });
    }
}
