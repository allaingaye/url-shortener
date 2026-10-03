<?php

// app/Console/Commands/PruneExpiredUrls.php

namespace App\Console\Commands;

use App\Models\Url;
use App\Services\UrlService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PruneExpiredUrls extends Command
{
    protected $signature = 'urls:prune-expired
                            {--dry-run : Show what would be deleted without deleting}
                            {--days=0 : Only delete URLs expired more than N days ago}';

    protected $description = 'Delete URLs whose expiration date has passed';

    public function __construct(
        private readonly UrlService $urlService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $dryRun = (bool) $this->option('dry-run');

        $cutoff = now()->subDays($days);

        $query = Url::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', $cutoff);

        $count = $query->count();

        if ($count === 0) {
            $this->info('No expired URLs found.');

            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->warn("Would delete {$count} expired URL(s).");
            $query->limit(10)->get(['id', 'short_code', 'expires_at'])
                ->each(fn ($u) => $this->line("  - #{$u->id} {$u->short_code} (expired {$u->expires_at})"));

            return self::SUCCESS;
        }

        // Bust cache for each URL before deleting
        $query->chunkById(100, function ($urls) {
            foreach ($urls as $url) {
                $this->urlService->forget($url->public_code);
            }
        });

        $deleted = Url::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', $cutoff)
            ->delete();

        $this->info("Deleted {$deleted} expired URL(s).");

        Log::info('Pruned expired URLs', [
            'count' => $deleted,
            'days' => $days,
        ]);

        return self::SUCCESS;
    }
}
