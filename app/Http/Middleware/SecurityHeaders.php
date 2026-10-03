<?php

// app/Http/Middleware/SecurityHeaders.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds security headers to every response.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        // TEMP DEBUG — remove after verifying

        $response = $next($request);

        // ── Standard security headers ──
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');

        // ── Content-Security-Policy ──
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; "
            ."script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://unpkg.com; "
            ."style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.bunny.net; "
            ."font-src 'self' data: https://fonts.bunny.net; "
            ."img-src 'self' data: https:; "
            ."connect-src 'self'; "
            ."frame-ancestors 'none';"
        );

        // ── Permissions-Policy ──
        $response->headers->set(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=(), payment=()'
        );

        // ── HSTS — only over HTTPS in non-local environments ──
        if ($this->shouldSendHsts($request)) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );
        }

        return $response;
    }

    private function shouldSendHsts(Request $request): bool
    {
        return ! app()->environment(['local', 'testing'])
            && $request->isSecure();
    }
}
