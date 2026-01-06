<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register custom middleware aliases
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        // Library loan due date notifications
        // Run twice daily: morning and afternoon
        $schedule->command('library:notify-due-dates')
            ->dailyAt('08:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/library-notifications.log'));
        
        $schedule->command('library:notify-due-dates')
            ->dailyAt('16:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/library-notifications.log'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
