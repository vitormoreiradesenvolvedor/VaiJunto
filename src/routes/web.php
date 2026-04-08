<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FixedRouteController;
use App\Http\Controllers\RideController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'))->name('home');
Route::get('/login', fn () => view('auth.login'))->name('login');

// OAuth Google
Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'callback']);

// Rotas protegidas por autenticação
Route::middleware('auth')->group(function () {
    Route::post('/logout', function () {
        Auth::logout();
        return redirect()->route('login');
    })->name('logout');

    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Caronas
    Route::get('/rides/create', [RideController::class, 'create'])->name('rides.create');
    Route::post('/rides/request', [RideController::class, 'store']);
    Route::post('/rides/{rideRequest}/accept', [RideController::class, 'accept']);
    Route::post('/rides/{rideRequest}/reject', [RideController::class, 'reject']);
    Route::post('/rides/{ride}/cancel', [RideController::class, 'cancel']);

    // Veículos
    Route::get('/vehicles/{vehicle}/edit', [VehicleController::class, 'edit'])->name('vehicles.edit');
    Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update']);

    // Rotas fixas
    Route::post('/routes/{fixedRoute}/pause', [FixedRouteController::class, 'pause']);
});
