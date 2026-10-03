<?php

// database/migrations/2026_09_29_XXXXXX_create_clicks_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Click events for shortened URLs.
     *
     * Each row is a single hit on a short code, with analytics metadata
     * so we can build stats like "top referrers" or "device breakdown".
     *
     * Indexing notes:
     *   - (url_id, created_at) — for per-URL, per-day queries
     *   - (browser), (device) — for grouping in the stats endpoint
     */
    public function up(): void
    {
        Schema::create('clicks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('url_id')
                ->constrained()
                ->cascadeOnDelete();

            // Who & where
            $table->string('ip_address', 45)->nullable();   // IPv6 fits in 45
            $table->string('user_agent', 512)->nullable();
            $table->string('referer', 2048)->nullable();    // e.g. https://google.com/...

            // Parsed from User-Agent
            $table->string('device', 50)->nullable();       // desktop, mobile, tablet, ...
            $table->string('browser', 50)->nullable();      // Chrome, Firefox, Safari, ...
            $table->string('platform', 50)->nullable();     // Windows, macOS, Linux, iOS, Android

            // Reserved for later
            $table->string('country', 2)->nullable();       // ISO 3166-1 alpha-2

            $table->timestamps();

            $table->index(['url_id', 'created_at']);
            $table->index('browser');
            $table->index('device');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clicks');
    }
};
