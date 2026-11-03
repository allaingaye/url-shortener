<?php

// routes/web.php

use App\Http\Controllers\RedirectController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| The only web route for this app is the short URL redirect.
| Dashboard, login, and other UI routes are handled by the React SPA.
|
*/

Route::get('/{code}', RedirectController::class)
    ->where('code', '[A-Za-z0-9_-]+')
    ->name('redirect');
