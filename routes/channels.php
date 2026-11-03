<?php

// routes/channels.php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
*/

/**
 * Private channel per user — only the owner can listen.
 * Channel name: `user.{userId}` (matches ClickRecorded::broadcastOn)
 */
Broadcast::channel('user.{userId}', function (User $user, int $userId) {
    return (int) $user->id === (int) $userId;
});
