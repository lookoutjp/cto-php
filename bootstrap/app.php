<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // web リクエストごとに現在のサイト(テナント)を確定させる。
        // Filament管理画面は AdminPanelProvider 側で別途登録している。
        $middleware->web(append: [
            \App\Http\Middleware\ResolveCurrentSite::class,
        ]);

        // Stripe Webhook は CSRF 検証から除外（Cashier のルートは path 'stripe'）。
        $middleware->validateCsrfTokens(except: [
            'stripe/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
