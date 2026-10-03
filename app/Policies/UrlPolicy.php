<?php
// app/Policies/UrlPolicy.php

namespace App\Policies;

use App\Models\Url;
use App\Models\User;

class UrlPolicy
{
    /**
     * Can the given user (or guest) view this URL?
     * - Anonymous URLs (user_id = null) are public.
     * - Owned URLs are visible only to their owner.
     */
    public function view(?User $user, Url $url): bool
    {
        if ($url->user_id === null) {
            return true;
        }

        return $user !== null && $user->id === $url->user_id;
    }

    /**
     * Only the owner can update.
     */
    public function update(User $user, Url $url): bool
    {
        return $user->id === $url->user_id;
    }

    /**
     * Only the owner can delete.
     */
    public function delete(User $user, Url $url): bool
    {
        return $user->id === $url->user_id;
    }
}