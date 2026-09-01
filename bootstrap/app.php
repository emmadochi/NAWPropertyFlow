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
        // Global handler: return JSON for all AJAX/XHR requests so the client
        // always sees a readable error message instead of an HTML 500 page.
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                $status  = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
                $message = $e->getMessage() ?: 'An unexpected server error occurred.';

                // Don't expose internal details in production for generic 500s
                if ($status === 500 && app()->environment('production')) {
                    \Illuminate\Support\Facades\Log::error('Unhandled AJAX exception', [
                        'message' => $e->getMessage(),
                        'file'    => $e->getFile() . ':' . $e->getLine(),
                    ]);
                    $message = 'Server error: ' . $e->getMessage() . ' (' . basename($e->getFile()) . ':' . $e->getLine() . ')';
                }

                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'error'   => class_basename($e),
                ], $status >= 400 ? $status : 500);
            }
        });
    })->create();
