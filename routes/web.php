<?php
// routes/web.php

use App\Http\Controllers\RedirectController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| The only web route is the short-URL redirect.
| It's throttled per IP to prevent hot-link flooding.
|
*/

Route::get('/{code}', RedirectController::class)
    ->where('code', '[A-Za-z0-9_-]+')
    ->middleware('throttle:redirect')
    ->name('redirect');