<?php

// routes/console.php

use App\Console\Commands\PruneExpiredUrls;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
*/

Schedule::command(PruneExpiredUrls::class)
    ->dailyAt('03:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->runInBackground();
