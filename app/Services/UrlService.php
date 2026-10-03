<?php
// app/Services/UrlService.php

namespace App\Services;

use App\Models\Url;
use Illuminate\Support\Facades\Cache;

/**
 * Orchestrates URL shortener business rules.
 *
 * Controllers should be thin; all logic lives here so it can be
 * tested in isolation and reused from jobs, commands, etc.
 */
class UrlService
{
    /** Cache TTL for resolved URL IDs. */
    private const CACHE_TTL_SECONDS = 3600; // 1 hour

    public function __construct(
        private readonly ShortCodeGenerator $generator,
    ) {}

    /**
     * Create a new shortened URL.
     *
     * @param  array{original_url: string, custom_alias?: ?string, expires_at?: ?string}  $data
     * @param  int|null  $userId  Null for anonymous (guest) URLs.
     */
    public function create(array $data, ?int $userId = null): Url
    {
        return Url::create([
            'user_id'      => $userId,
            'original_url' => $data['original_url'],
            'short_code'   => $this->generator->generate(),
            'custom_alias' => $data['custom_alias'] ?? null,
            'expires_at'   => $data['expires_at']   ?? null,
            'is_active'    => true,
        ]);
    }

    /**
     * Resolve a public code (short_code OR custom_alias) to its Url.
     *
     * Returns null if the code doesn't exist, is inactive, or has expired.
     *
     * Implementation note:
     * We cache the URL **ID**, not the model instance. Eloquent models
     * serialize poorly across cache drivers (they carry internal state),
     * so we store the primitive and rehydrate via a fast indexed lookup.
     */
    public function resolve(string $code): ?Url
    {
        $urlId = Cache::remember(
            $this->cacheKey($code),
            self::CACHE_TTL_SECONDS,
            fn () => Url::query()
                ->active()
                ->notExpired()
                ->byCode($code)
                ->value('id') // ← store just the ID
        );

        if ($urlId === null) {
            return null;
        }

        return Url::query()
            ->active()
            ->notExpired()
            ->find($urlId);
    }

    /**
     * Invalidate the cached resolution for a code.
     * Call this whenever a URL is updated or deleted.
     */
    public function forget(string $code): void
    {
        Cache::forget($this->cacheKey($code));
    }

    /**
     * Consistent cache key namespace.
     */
    private function cacheKey(string $code): string
    {
        return "url:resolve:{$code}";
    }
}