<?php

// app/Services/ShortCodeGenerator.php

namespace App\Services;

use App\Models\Url;
use RuntimeException;

/**
 * Generates short codes for URLs.
 *
 * Extracted from UrlService so the algorithm can be swapped
 * (random, base62 of ID, hashids, etc.) without touching callers.
 */
class ShortCodeGenerator
{
    /**
     * Length of generated codes.
     * 7 chars of [A-Za-z0-9] ≈ 62^7 ≈ 3.5 trillion combinations.
     */
    private const CODE_LENGTH = 7;

    /**
     * How many times to retry on a collision before giving up.
     * With a 62^7 space, a collision is astronomically unlikely,
     * but we handle it gracefully anyway.
     */
    private const MAX_ATTEMPTS = 5;

    /**
     * Generate a unique, URL-safe short code.
     *
     * @throws RuntimeException when we cannot find a unique code
     */
    public function generate(): string
    {
        for ($attempt = 0; $attempt < self::MAX_ATTEMPTS; $attempt++) {
            $code = $this->randomCode();

            if (! $this->exists($code)) {
                return $code;
            }
        }

        throw new RuntimeException(
            'Unable to generate a unique short code after '.self::MAX_ATTEMPTS.' attempts.'
        );
    }

    /**
     * Produce a random URL-safe string.
     *
     * Uses random_int (cryptographically secure) instead of rand/mt_rand.
     */
    private function randomCode(): string
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $max = strlen($alphabet) - 1;
        $code = '';

        for ($i = 0; $i < self::CODE_LENGTH; $i++) {
            $code .= $alphabet[random_int(0, $max)];
        }

        return $code;
    }

    /**
     * Check whether a code is already in use (as short_code OR custom_alias).
     */
    private function exists(string $code): bool
    {
        return Url::query()
            ->where('short_code', $code)
            ->orWhere('custom_alias', $code)
            ->exists();
    }
}
