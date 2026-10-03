<?php

// routes/api.php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UrlController;
use App\Http\Controllers\Api\UrlStatsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Throttling is applied per-group:
|   - auth/register : 5/min per IP     → throttle:register
|   - auth/login    : 5/min per IP+email → throttle:login
|   - all others    : 60/min per IP    → throttle:api
|   - authenticated : 120/min per user → throttle:api-user
|
*/

Route::prefix('v1')->group(function () {

    // ── Auth ──
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register'])
            ->middleware('throttle:register')
            ->name('auth.register');

        Route::post('login', [AuthController::class, 'login'])
            ->middleware('throttle:login')
            ->name('auth.login');

        Route::middleware(['auth:sanctum', 'throttle:api-user'])->group(function () {
            Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout');
            Route::get('me', [AuthController::class, 'me'])->name('auth.me');
        });
    });

    // ── Public: guest shorten (IP-throttled) ──
    Route::post('urls', [UrlController::class, 'store'])
        ->middleware('throttle:api')
        ->name('urls.store');

    // ── Authenticated: manage owned URLs (user-throttled) ──
    Route::middleware(['auth:sanctum', 'throttle:api-user'])->group(function () {
        Route::get('urls', [UrlController::class, 'index'])->name('urls.index');
        Route::get('urls/{url}/stats', UrlStatsController::class)->name('urls.stats');
        Route::get('urls/{url}', [UrlController::class, 'show'])->name('urls.show');
        Route::patch('urls/{url}', [UrlController::class, 'update'])->name('urls.update');
        Route::delete('urls/{url}', [UrlController::class, 'destroy'])->name('urls.destroy');
    });
});
