<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\System\SystemAuthController;
use App\Http\Controllers\System\TenantController;

/*
|--------------------------------------------------------------------------
| Central / Landlord Routes
|--------------------------------------------------------------------------
|
| These routes run on the central domain (localhost / app.nawpropertyflow.com).
| They serve:
|   1. The public marketing landing page
|   2. The System Admin portal for NAW World Technologies staff
|
| All CRM routes (dashboard, leads, HR, etc.) are in routes/tenant.php
| and only activate when accessed on a tenant subdomain.
|
*/

// ─── PWA Manifest & Service Worker Direct Endpoints ──────────────────────
Route::get('/manifest.json', function () {
    $path = public_path('manifest.json');
    if (file_exists($path)) {
        return response()->file($path, [
            'Content-Type' => 'application/manifest+json; charset=utf-8',
        ]);
    }
    return response()->json([
        'name' => config('app.name', 'RICAF PropertyFlow CRM'),
        'short_name' => 'RICAF CRM',
        'id' => '/',
        'start_url' => '/',
        'scope' => '/',
        'display' => 'standalone',
        'background_color' => '#FFFFFF',
        'theme_color' => '#F37021',
        'icons' => [
            ['src' => '/icons/icon-192x192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
            ['src' => '/icons/icon-512x512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
        ]
    ]);
});

Route::get('/sw.js', function () {
    $path = public_path('sw.js');
    if (file_exists($path)) {
        return response()->file($path, [
            'Content-Type' => 'application/javascript; charset=utf-8',
            'Service-Worker-Allowed' => '/',
        ]);
    }
    return response("self.addEventListener('fetch', () => {});", 200, [
        'Content-Type' => 'application/javascript; charset=utf-8',
    ]);
});

// ─── CRM Routes (Standalone Mode) ───────────────────────────────────────────
if (! env('TENANCY_ENABLED', false)) {
    require __DIR__.'/tenant.php';
} else {
    // ─── Public Landing Page (Multi-Tenant Landlord) ─────────────────────────
    Route::get('/', function () {
        return view('welcome');
    })->name('home');
}

// ─── System Admin Authentication ─────────────────────────────────────────────
Route::prefix('system')->name('system.')->group(function () {
    // Guest routes
    Route::middleware('guest:system_admin')->group(function () {
        Route::get('/login', [SystemAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [SystemAuthController::class, 'login'])->name('login.submit');
    });

    // Authenticated routes
    Route::middleware('auth:system_admin')->group(function () {
        Route::post('/logout', [SystemAuthController::class, 'logout'])->name('logout');

        // Dashboard
        Route::get('/dashboard', [TenantController::class, 'dashboard'])->name('dashboard');

        // Tenant management
        Route::get('/tenants/create', [TenantController::class, 'create'])->name('tenants.create');
        Route::get('/tenants', function () { return redirect()->route('system.dashboard'); });
        Route::post('/tenants', [TenantController::class, 'store'])->name('tenants.store');
        Route::patch('/tenants/{tenant}/toggle', [TenantController::class, 'toggleStatus'])->name('tenants.toggle');
        Route::patch('/tenants/{tenant}/upgrade', [TenantController::class, 'upgradePlan'])->name('tenants.upgrade');
        Route::delete('/tenants/{tenant}', [TenantController::class, 'destroy'])->name('tenants.destroy');
    });
});
