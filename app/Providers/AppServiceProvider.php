<?php
// app/Providers/AppServiceProvider.php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerRateLimiters();
    }

    /**
     * Named rate limiters, applied via `throttle:<name>` middleware.
     *
     * Design:
     *   - `login`     : 5 attempts / min per IP + email   → brute-force protection
     *   - `register`  : 5 attempts / min per IP            → spam-account prevention
     *   - `api`       : 60 / min per IP                    → public API abuse
     *   - `api-user`  : 120 / min per user (falls back to IP for guests)
     *   - `redirect`  : 300 / min per IP                   → hot-link flood
     *
     * All limits use Laravel's built-in response format:
     *   HTTP 429 with `Retry-After` header and JSON body.
     */
    private function registerRateLimiters(): void
    {
        // ── Login: 5 per minute per (IP + email) ──
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->input('email');

            return Limit::perMinute(5)
                ->by($request->ip() . '|' . strtolower($email))
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'message' => 'Too many login attempts. Please try again later.',
                    ], 429, $headers);
                });
        });

        // ── Register: 5 per minute per IP ──
        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'message' => 'Too many accounts created from this IP. Please try again later.',
                    ], 429, $headers);
                });
        });

        // ── Public API: 60 per minute per IP ──
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)
                ->by($request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'message' => 'Too many requests. Please slow down.',
                    ], 429, $headers);
                });
        });

        // ── Authenticated API: 120 per minute per user, fallback to IP ──
        RateLimiter::for('api-user', function (Request $request) {
            $key = $request->user()?->id ?? $request->ip();

            return Limit::perMinute(120)
                ->by((string) $key)
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'message' => 'You have exceeded your rate limit.',
                    ], 429, $headers);
                });
        });

        // ── Redirect: 300 per minute per IP ──
        RateLimiter::for('redirect', function (Request $request) {
            return Limit::perMinute(300)
                ->by($request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'message' => 'Too many redirect requests. Please slow down.',
                    ], 429, $headers);
                });
        });
    }
}