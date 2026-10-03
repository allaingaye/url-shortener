<?php

// app/Http/Controllers/RedirectController.php

namespace App\Http\Controllers;

use App\Jobs\RecordClick;
use App\Services\UrlService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RedirectController extends Controller
{
    public function __construct(
        private readonly UrlService $urlService,
    ) {}

    public function __invoke(Request $request, string $code): RedirectResponse
    {
        $url = $this->urlService->resolve($code);

        if (! $url) {
            throw new NotFoundHttpException('Short URL not found, expired, or inactive.');
        }

        // 1. Denormalized counter — cheap atomic increment (synchronous, fast).
        $url->increment('clicks_count');

        // 2. Detailed click event — dispatched to the queue (asynchronous).
        RecordClick::dispatch(
            $url->id,
            $request->ip() ?? '0.0.0.0',
            (string) $request->userAgent(),
            $request->header('referer'),
        );

        // 3. Send the visitor on their way — no blocking on the DB write.
        return redirect()->away($url->original_url, 302);
    }
}
