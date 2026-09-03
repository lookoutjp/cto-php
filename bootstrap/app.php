<?php

use App\Http\Middleware\ResolveCurrentSite;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule): void {
        // 定例作業の自動生成（全サイト・既定40日先まで、重複作成なし）
        $schedule->command('routinework:generate')->dailyAt('00:10')->withoutOverlapping();

        // 添付先が消えた孤児ファイルの掃除
        $schedule->command('attachments:prune')->weeklyOn(1, '03:00');
    })
    ->withMiddleware(function (Middleware $middleware): void {
        // web リクエストごとに現在のサイト(テナント)を確定させる。
        // Filament管理画面は AdminPanelProvider 側で別途登録している。
        $middleware->web(append: [
            ResolveCurrentSite::class,
        ]);

        // Stripe Webhook は CSRF 検証から除外（Cashier のルートは path 'stripe'）。
        $middleware->validateCsrfTokens(except: [
            'stripe/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
