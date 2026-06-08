<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FixedRouteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\RideController;
use App\Http\Controllers\TripController;
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
    Route::get('/driver/pending-requests', [DashboardController::class, 'pendingRequests'])->name('driver.pending-requests');
    Route::get('/passenger/available-offers', [DashboardController::class, 'availableOffers'])->name('passenger.available-offers');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/users/{user}/profile', [ProfileController::class, 'show'])->name('profile.user');
    Route::get('/users/{user}/reputation', [ProfileController::class, 'reputation'])->name('profile.reputation');
    Route::get('/mode/{mode}', function (string $mode) {
        abort_if(!in_array($mode, ['passenger', 'driver']), 422);
        session(['user_mode' => $mode]);
        return redirect()->route('dashboard');
    })->name('mode.set');

    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');

    // Proxy server-side para Directions API (evita restrições de referrer do navegador)
    Route::get('/api/directions', [RideController::class, 'directions'])->name('rides.directions');

    // Caronas
    Route::get('/rides/create', [RideController::class, 'create'])->name('rides.create');
    Route::post('/rides/request', [RideController::class, 'store']);
    Route::get('/rides/{rideRequest}/track', [RideController::class, 'track'])->name('rides.track');
    Route::get('/rides/{rideRequest}/status', [RideController::class, 'status'])->name('rides.status');
    Route::post('/rides/{rideRequest}/accept', [RideController::class, 'accept']);
    Route::post('/rides/{rideRequest}/reject', [RideController::class, 'reject']);
    Route::post('/rides/{rideRequest}/cancel-request', [RideController::class, 'cancelRequest'])->name('rides.cancel-request');
    // Gerenciamento da carona pelo motorista
    Route::get('/rides/{ride}/drive', [RideController::class, 'drive'])->name('rides.drive');
    Route::get('/rides/{ride}/status-driver', [RideController::class, 'statusForDriver'])->name('rides.status-driver');
    Route::post('/rides/{ride}/arrived', [RideController::class, 'arrived'])->name('rides.arrived');
    Route::post('/rides/{ride}/boarded', [RideController::class, 'boarded'])->name('rides.boarded');
    Route::post('/rides/{ride}/start', [RideController::class, 'start'])->name('rides.start');
    Route::post('/rides/{ride}/finish', [RideController::class, 'finish'])->name('rides.finish');
    Route::post('/rides/{ride}/location', [RideController::class, 'updateLocation'])->name('rides.location');
    Route::post('/rides/{ride}/cancel', [RideController::class, 'cancel']);
    // Avaliação
    Route::get('/rides/{ride}/rate', [RatingController::class, 'create'])->name('rides.rate');
    Route::post('/rides/{ride}/rate', [RatingController::class, 'store'])->name('rides.rate.store');

    // Viagens (motorista oferece)
    Route::get('/trips/create', [TripController::class, 'create'])->name('trips.create');
    Route::post('/trips', [TripController::class, 'store'])->name('trips.store');
    Route::get('/trips/{trip}', [TripController::class, 'show'])->name('trips.show');
    Route::post('/trips/{trip}/cancel', [TripController::class, 'cancel'])->name('trips.cancel');
    Route::post('/trips/{trip}/join', [TripController::class, 'join'])->name('trips.join');
    Route::post('/trips/{trip}/requests/{rideRequest}/accept', [TripController::class, 'acceptRequest']);
    Route::post('/trips/{trip}/requests/{rideRequest}/reject', [TripController::class, 'rejectRequest']);
    Route::get('/trips/{trip}/pending', [TripController::class, 'pendingRequests'])->name('trips.pending');

    // Veículos
    Route::get('/vehicles/create', [VehicleController::class, 'create'])->name('vehicles.create');
    Route::post('/vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
    Route::get('/vehicles/{vehicle}/edit', [VehicleController::class, 'edit'])->name('vehicles.edit');
    Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update']);

    // Rotas fixas (recorrentes)
    Route::get('/routes/create', [FixedRouteController::class, 'create'])->name('routes.create');
    Route::post('/routes', [FixedRouteController::class, 'store'])->name('routes.store');
    Route::get('/routes/{fixedRoute}', [FixedRouteController::class, 'show'])->name('routes.show');
    Route::patch('/routes/{fixedRoute}/toggle', [FixedRouteController::class, 'toggleStatus'])->name('routes.toggle');
    Route::post('/routes/{fixedRoute}/join', [FixedRouteController::class, 'join'])->name('routes.join');
    Route::post('/routes/{fixedRoute}/requests/{rideRequest}/accept', [FixedRouteController::class, 'acceptRequest'])->name('routes.requests.accept');
    Route::post('/routes/{fixedRoute}/requests/{rideRequest}/reject', [FixedRouteController::class, 'rejectRequest'])->name('routes.requests.reject');
    Route::post('/routes/{fixedRoute}/pause', [FixedRouteController::class, 'pause']);
    Route::post('/routes/{fixedRoute}/cancel', [FixedRouteController::class, 'cancel'])->name('routes.cancel');
    Route::get('/routes/{fixedRoute}/pending', [FixedRouteController::class, 'pendingRequests'])->name('routes.pending');
});
