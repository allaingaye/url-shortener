<?php

// tests/Feature/Api/AnalyticsTest.php

use App\Jobs\RecordClick;
use App\Models\Click;
use App\Models\Url;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

it('dispatches a job when a short URL is visited', function () {
    Queue::fake();
    $url = Url::factory()->create(['short_code' => 'track']);

    $this->get('/track', [
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0) Chrome/120.0',
        'Referer' => 'https://google.com/',
    ])->assertRedirect();

    Queue::assertPushed(RecordClick::class, fn ($job) => $job->urlId === $url->id);
});

it('detects mobile devices from user agent', function () {
    $url = Url::factory()->create(['short_code' => 'mobile']);

    // Execute the job synchronously to verify the UA parsing
    RecordClick::dispatchSync(
        $url->id,
        '127.0.0.1',
        'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 Version/17.0 Mobile/15E148 Safari/604.1',
    );

    $click = Click::first();
    expect($click->device)->toBe('mobile');
    expect($click->browser)->toBe('Safari');
    expect($click->platform)->toBe('iOS');
});

it('records multiple clicks on the same URL', function () {
    $url = Url::factory()->create(['short_code' => 'many']);

    for ($i = 0; $i < 3; $i++) {
        RecordClick::dispatchSync($url->id, '127.0.0.1', 'test-agent');
    }

    expect(Click::count())->toBe(3);
});

// ─── STATS ENDPOINT ─────────────────────────────────────

it('returns aggregated stats for the URL owner', function () {
    $jane = User::factory()->create();
    $url = Url::factory()->ownedBy($jane)->create(['short_code' => 'st']);

    Click::factory()->for($url)->count(3)->create(['browser' => 'Chrome']);
    Click::factory()->for($url)->count(2)->create(['browser' => 'Firefox']);

    $response = $this->actingAs($jane, 'sanctum')
        ->getJson("/api/v1/urls/{$url->id}/stats");

    $response->assertOk()
        ->assertJsonPath('data.total_clicks', 5)
        ->assertJsonStructure([
            'data' => [
                'url',
                'total_clicks',
                'unique_visitors',
                'by_device',
                'by_browser',
                'by_platform',
                'top_referers',
                'by_day',
            ],
        ]);

    expect($response->json('data.by_browser.Chrome'))->toBe(3);
    expect($response->json('data.by_browser.Firefox'))->toBe(2);
});

it('forbids viewing stats for another user\'s URL', function () {
    $jane = User::factory()->create();
    $bob = User::factory()->create();
    $url = Url::factory()->ownedBy($jane)->create();

    $this->actingAs($bob, 'sanctum')
        ->getJson("/api/v1/urls/{$url->id}/stats")
        ->assertForbidden();
});

it('rejects unauthenticated stats access', function () {
    $url = Url::factory()->create();

    $this->getJson("/api/v1/urls/{$url->id}/stats")
        ->assertUnauthorized();
});
