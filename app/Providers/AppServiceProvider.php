<?php

namespace App\Providers;

use App\Auth\Passwords\CustomPasswordBrokerManager;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
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
        //
    }
}
