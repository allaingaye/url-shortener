<?php

// tests/Feature/Api/QrCodeTest.php

use App\Models\Url;
use App\Models\User;
use App\Services\QrCodeService;

it('returns a PNG QR code for the owner', function () {
    $jane = User::factory()->create();
    $url = Url::factory()->ownedBy($jane)->create([
        'short_code' => 'qr-test',
    ]);

    $response = $this->actingAs($jane, 'sanctum')
        ->get("/api/v1/urls/{$url->id}/qr");

    $response->assertOk()
        ->assertHeader('Content-Type', 'image/png');

    expect($response->headers->get('Cache-Control'))
        ->toContain('max-age=86400')
        ->toContain('public');

    $content = $response->getContent();
    expect($content)->toStartWith("\x89PNG\r\n\x1a\n");
});

it('returns an SVG QR code when requested', function () {
    $jane = User::factory()->create();
    $url = Url::factory()->ownedBy($jane)->create();

    $response = $this->actingAs($jane, 'sanctum')
        ->get("/api/v1/urls/{$url->id}/qr?format=svg");

    $response->assertOk()
        ->assertHeader('Content-Type', 'image/svg+xml');

    $content = $response->getContent();
    expect($content)
        ->toContain('<svg')
        ->toContain('xmlns="http://www.w3.org/2000/svg"');
});

it('respects the size parameter for PNG', function () {
    $jane = User::factory()->create();
    $url = Url::factory()->ownedBy($jane)->create();

    $small = $this->actingAs($jane, 'sanctum')
        ->get("/api/v1/urls/{$url->id}/qr?size=100");
    $large = $this->actingAs($jane, 'sanctum')
        ->get("/api/v1/urls/{$url->id}/qr?size=800");

    $small->assertOk();
    $large->assertOk();

    $smallSize = strlen($small->getContent());
    $largeSize = strlen($large->getContent());

    expect($largeSize)->toBeGreaterThan($smallSize);
});

it('clamps size to safe bounds', function () {
    $jane = User::factory()->create();
    $url = Url::factory()->ownedBy($jane)->create();

    $this->actingAs($jane, 'sanctum')
        ->get("/api/v1/urls/{$url->id}/qr?size=10")
        ->assertOk();

    $this->actingAs($jane, 'sanctum')
        ->get("/api/v1/urls/{$url->id}/qr?size=99999")
        ->assertOk();
});

it('falls back to PNG for invalid format', function () {
    $jane = User::factory()->create();
    $url = Url::factory()->ownedBy($jane)->create();

    $this->actingAs($jane, 'sanctum')
        ->get("/api/v1/urls/{$url->id}/qr?format=gif")
        ->assertOk()
        ->assertHeader('Content-Type', 'image/png');
});

it('rejects a non-owner from fetching the QR code', function () {
    $jane = User::factory()->create();
    $bob = User::factory()->create();
    $url = Url::factory()->ownedBy($jane)->create();

    $this->actingAs($bob, 'sanctum')
        ->get("/api/v1/urls/{$url->id}/qr")
        ->assertForbidden();
});

it('rejects unauthenticated requests', function () {
    $url = Url::factory()->create();

    $this->get("/api/v1/urls/{$url->id}/qr")
        ->assertUnauthorized();
});

it('returns 404 for a missing URL', function () {
    $jane = User::factory()->create();

    $this->actingAs($jane, 'sanctum')
        ->get('/api/v1/urls/999999/qr')
        ->assertNotFound();
});

it('includes the short URL content in the QR code', function () {
    $jane = User::factory()->create();
    $url = Url::factory()->ownedBy($jane)->create([
        'short_code' => 'qrcode123',
        'custom_alias' => 'my-qr-alias',
    ]);

    expect($url->short_url)->toContain('my-qr-alias');

    $this->actingAs($jane, 'sanctum')
        ->get("/api/v1/urls/{$url->id}/qr")
        ->assertOk();
});

it('caches QR codes in Redis for repeated requests', function () {
    $jane = User::factory()->create();
    $url = Url::factory()->ownedBy($jane)->create(['short_code' => 'cached-qr']);

    $service = app(QrCodeService::class);

    $start = microtime(true);
    $service->generate($url, 'png', 300);
    $firstMs = (microtime(true) - $start) * 1000;

    $start = microtime(true);
    $service->generate($url, 'png', 300);
    $secondMs = (microtime(true) - $start) * 1000;

    expect($secondMs)->toBeLessThan($firstMs);
});
