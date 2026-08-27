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
    ->withCommands([
        __DIR__.'/../app/Console/Commands',
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role'       => \App\Http\Middleware\RoleMiddleware::class,
            'permission' => \App\Http\Middleware\PermissionMiddleware::class,
            'feature'    => \App\Http\Middleware\CheckFeatureAccess::class,
        ]);

        // Dynamically redirect based on context
        $middleware->redirectGuestsTo(function (\Illuminate\Http\Request $request) {
            if ($request->is('supplier*')) {
                return route('supplier.login');
            }
            return route('login');
        });

        // Redirect authenticated users
        $middleware->redirectUsersTo(function (\Illuminate\Http\Request $request) {
            if (auth('supplier')->check()) {
                return route('supplier.dashboard');
            }
            if (auth()->check() && auth()->user()->role === 'customer') {
                return route('buyer.dashboard');
            }
            return route('dashboard');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
