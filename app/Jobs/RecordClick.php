<?php

// app/Jobs/RecordClick.php

namespace App\Jobs;

use App\Models\Url;
use App\Services\AnalyticsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RecordClick implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $backoff = 5;

    public bool $deleteWhenMissingModels = true;

    public function __construct(
        public readonly int $urlId,
        public readonly string $ip,
        public readonly string $userAgent,
        public readonly ?string $referer = null,
    ) {}

    public function handle(AnalyticsService $analytics): void
    {
        $url = Url::find($this->urlId);

        if (! $url) {
            return;
        }

        $analytics->record(
            url: $url,
            ip: $this->ip,
            userAgent: $this->userAgent,
            referer: $this->referer,
        );
    }
}
