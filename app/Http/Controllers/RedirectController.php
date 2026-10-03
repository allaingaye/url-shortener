<?php
// app/Http/Controllers/RedirectController.php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use App\Services\UrlService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RedirectController extends Controller
{
    public function __construct(
        private readonly UrlService $urlService,
        private readonly AnalyticsService $analytics,
    ) {}

    public function __invoke(Request $request, string $code): RedirectResponse
    {
        $url = $this->urlService->resolve($code);

        if (! $url) {
            throw new NotFoundHttpException('Short URL not found, expired, or inactive.');
        }

        // 1. Denormalized counter (cheap atomic increment)
        $url->increment('clicks_count');

        // 2. Detailed click event — currently synchronous; queued in Phase 7.
        $this->analytics->record(
            url:       $url,
            ip:        $request->ip() ?? '0.0.0.0',
            userAgent: (string) $request->userAgent(),
            referer:   $request->header('referer'),
        );

        // 3. Send the visitor on their way
        return redirect()->away($url->original_url, 302);
    }
}