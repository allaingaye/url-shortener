<?php

// tests/Feature/Console/PruneExpiredUrlsTest.php

use App\Models\Url;
use Illuminate\Support\Facades\Cache;

it('deletes expired URLs', function () {
    $expired = Url::factory()->expired()->create(['short_code' => 'old']);
    $active = Url::factory()->create(['short_code' => 'keep']);

    $this->artisan('urls:prune-expired')->assertSuccessful();

    expect(Url::find($expired->id))->toBeNull();
    expect(Url::find($active->id))->not->toBeNull();
});

it('does nothing when there are no expired URLs', function () {
    Url::factory()->count(3)->create();

    $this->artisan('urls:prune-expired')
        ->expectsOutput('No expired URLs found.')
        ->assertSuccessful();

    expect(Url::count())->toBe(3);
});

it('does not delete URLs with no expiration', function () {
    $permanent = Url::factory()->create(['expires_at' => null]);

    $this->artisan('urls:prune-expired')->assertSuccessful();

    expect(Url::find($permanent->id))->not->toBeNull();
});

it('respects the --days option', function () {
    $recentlyExpired = Url::factory()->create([
        'expires_at' => now()->subDay(),
        'short_code' => 'recent',
    ]);

    $longExpired = Url::factory()->create([
        'expires_at' => now()->subDays(10),
        'short_code' => 'ancient',
    ]);

    $this->artisan('urls:prune-expired', ['--days' => 5])->assertSuccessful();

    expect(Url::find($recentlyExpired->id))->not->toBeNull();
    expect(Url::find($longExpired->id))->toBeNull();
});

it('supports --dry-run without deleting', function () {
    Url::factory()->expired()->count(2)->create();

    $this->artisan('urls:prune-expired', ['--dry-run' => true])->assertSuccessful();

    expect(Url::count())->toBe(2);
});

it('busts the cache when deleting', function () {
    $url = Url::factory()->expired()->create(['short_code' => 'cached']);
    Cache::put("url:resolve:{$url->short_code}", $url->id, 3600);

    $this->artisan('urls:prune-expired')->assertSuccessful();

    expect(Cache::get("url:resolve:{$url->short_code}"))->toBeNull();
});
