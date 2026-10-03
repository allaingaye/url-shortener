<?php

// tests/Feature/Api/AnalyticsTest.php

use App\Models\Click;
use App\Models\Url;
use App\Models\User;

it('records a click when a short URL is visited', function () {
    $url = Url::factory()->create(['short_code' => 'track']);

    $this->get('/track', [
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0) Chrome/120.0',
        'Referer' => 'https://google.com/',
    ]);

    expect(Click::count())->toBe(1);

    $click = Click::first();
    expect($click->url_id)->toBe($url->id);
    expect($click->browser)->toBe('Chrome');
    expect($click->platform)->toBe('Windows');
    expect($click->device)->toBe('desktop');
    expect($click->referer)->toBe('https://google.com/');
});

it('detects mobile devices from user agent', function () {
    $url = Url::factory()->create(['short_code' => 'mobile']);

    $this->get('/mobile', [
        'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 Version/17.0 Mobile/15E148 Safari/604.1',
    ]);

    $click = Click::first();
    expect($click->device)->toBe('mobile');
    expect($click->browser)->toBe('Safari');
    expect($click->platform)->toBe('iOS');
});

it('records multiple clicks on the same URL', function () {
    Url::factory()->create(['short_code' => 'many']);

    $this->get('/many');
    $this->get('/many');
    $this->get('/many');

    expect(Click::count())->toBe(3);
});

// ─── STATS ENDPOINT ─────────────────────────────────────

it('returns aggregated stats for the URL owner', function () {
    $jane = User::factory()->create();
    $url = Url::factory()->ownedBy($jane)->create(['short_code' => 'st']);

    // Create some clicks with varied attributes
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
