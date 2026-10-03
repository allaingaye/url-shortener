<?php

// app/Services/AnalyticsService.php

namespace App\Services;

use App\Models\Click;
use App\Models\Url;
use Illuminate\Support\Facades\Log;
use Jenssegers\Agent\Agent;

/**
 * Records click analytics for shortened URLs.
 *
 * Extracted from the redirect controller so it can:
 *   - be dispatched from a queued job (Phase 7.6)
 *   - be unit-tested independently of HTTP
 */
class AnalyticsService
{
    /**
     * Record a single click event.
     *
     * @param  Url  $url  The URL that was hit.
     * @param  string  $ip  Client IP (v4 or v6).
     * @param  string  $userAgent  Raw User-Agent header value.
     * @param  ?string  $referer  Raw Referer header value (may be null).
     */
    public function record(
        Url $url,
        string $ip,
        string $userAgent,
        ?string $referer = null,
    ): Click {
        $agent = $this->parseUserAgent($userAgent);

        $click = Click::create([
            'url_id' => $url->id,
            'ip_address' => $ip,
            'user_agent' => mb_substr($userAgent, 0, 512),
            'referer' => $referer ? mb_substr($referer, 0, 2048) : null,
            'device' => $agent['device'],
            'browser' => $agent['browser'],
            'platform' => $agent['platform'],
            'country' => null, // populated later (GeoIP)
        ]);

        Log::channel('clicks')->info('Click recorded', [
            'url_id' => $url->id,
            'public_code' => $url->public_code,
            'ip' => $ip,
            'browser' => $agent['browser'],
            'platform' => $agent['platform'],
            'device' => $agent['device'],
            'referer' => $referer,
        ]);

        return $click;
    }

    /**
     * Extract device / browser / platform from a User-Agent string.
     *
     * @return array{device: ?string, browser: ?string, platform: ?string}
     */
    private function parseUserAgent(string $userAgent): array
    {
        if ($userAgent === '') {
            return ['device' => null, 'browser' => null, 'platform' => null];
        }

        $agent = new Agent;
        $agent->setUserAgent($userAgent);

        return [
            'device' => $this->detectDevice($agent),
            'browser' => $agent->browser() ?: null,
            'platform' => $agent->platform() ?: null,
        ];
    }

    /**
     * Classify the device into desktop / mobile / tablet / bot.
     */
    private function detectDevice(Agent $agent): ?string
    {
        return match (true) {
            $agent->isRobot() => 'bot',
            $agent->isTablet() => 'tablet',
            $agent->isMobile() => 'mobile',
            $agent->isDesktop() => 'desktop',
            default => null,
        };
    }
}
