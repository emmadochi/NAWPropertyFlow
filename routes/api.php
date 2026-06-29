<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\InspectionController;
use App\Http\Controllers\Api\SalesController;

Route::name('api.')->group(function () {
    // Public Auth Endpoints
    Route::post('/login', [AuthController::class, 'login'])->name('login');

    // Authenticated Endpoints
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        
        Route::get('/user', function (Request $request) {
            return $request->user();
        })->name('user');

        // Leads API
        Route::apiResource('leads', LeadController::class);

        // Properties API (Read-only)
        Route::get('properties', [PropertyController::class, 'index'])->name('properties.index');
        Route::get('properties/{property}', [PropertyController::class, 'show'])->name('properties.show');

        // Inspections API
        Route::get('inspections', [InspectionController::class, 'index'])->name('inspections.index');
        Route::post('inspections', [InspectionController::class, 'store'])->name('inspections.store');

        // Sales API
        Route::get('sales', [SalesController::class, 'index'])->name('sales.index');
        Route::post('sales', [SalesController::class, 'store'])->name('sales.store');
    });
});
