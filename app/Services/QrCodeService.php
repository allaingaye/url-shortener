<?php

// app/Services/QrCodeService.php

namespace App\Services;

use App\Models\Url;
use Illuminate\Support\Facades\Cache;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * Generates and caches QR codes for shortened URLs.
 *
 * QR codes are derived data — deterministic per (URL, format, size).
 * We cache the base64 output in Redis so repeat requests are instant.
 */
class QrCodeService
{
    /** Cache TTL: 24 hours. QR codes rarely change. */
    private const CACHE_TTL_SECONDS = 86400;

    /**
     * Generate a base64-encoded QR code for the URL.
     *
     * @param  Url  $url  The URL to encode.
     * @param  string  $format  'png' or 'svg'.
     * @param  int  $size  Pixels (only used for PNG; ignored for SVG).
     * @return string Base64 data URI (e.g. "data:image/png;base64,...").
     */
    public function generate(Url $url, string $format = 'png', int $size = 300): string
    {
        // Sanitize input
        $format = in_array($format, ['png', 'svg'], true) ? $format : 'png';
        $size = max(64, min(1024, $size));

        $cacheKey = $this->cacheKey($url, $format, $size);

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($url, $format, $size) {
            return $this->render($url, $format, $size);
        });
    }

    /**
     * Invalidate the cached QR code for a URL.
     * Call this when the URL's public code changes (e.g. alias update).
     */
    public function forget(Url $url): void
    {
        Cache::forget($this->cacheKey($url, 'png', 300));
        Cache::forget($this->cacheKey($url, 'svg', 300));

        foreach ([128, 256, 512, 1024] as $size) {
            Cache::forget($this->cacheKey($url, 'png', $size));
        }
    }

    /**
     * Render the QR code without caching.
     */
    private function render(Url $url, string $format, int $size): string
    {
        $content = $url->short_url;

        return QrCode::format($format)
            ->size($size)
            ->margin(1)
            ->errorCorrection('H')
            ->generate($content);
    }

    /**
     * Consistent cache key.
     */
    private function cacheKey(Url $url, string $format, int $size): string
    {
        return "qrcode:{$url->public_code}:{$format}:{$size}";
    }
}
