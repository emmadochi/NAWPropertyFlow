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
        $middleware->alias([
            'role'    => \App\Http\Middleware\RoleMiddleware::class,
            'feature' => \App\Http\Middleware\CheckFeatureAccess::class,
        ]);

        // System admin redirect: not authenticated → /system/login
        // Tenant user redirect: handled by tenant.php guest middleware → /login
        $middleware->redirectGuestsTo(fn () => route('system.login'));

        // Redirect authenticated system admins to system dashboard
        $middleware->redirectUsersTo(fn () => route('system.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
