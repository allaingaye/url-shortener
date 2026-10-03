<?php

// tests/Feature/Middleware/SecurityHeadersTest.php

it('adds security headers to every response', function () {
    $response = $this->get('/up');

    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('X-Frame-Options', 'DENY');
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    $response->assertHeader('X-Permitted-Cross-Domain-Policies', 'none');
});

it('adds a Content-Security-Policy header', function () {
    $response = $this->get('/up');

    expect($response->headers->get('Content-Security-Policy'))
        ->toContain("default-src 'self'")
        ->toContain("frame-ancestors 'none'");
});

it('adds a Permissions-Policy header', function () {
    $response = $this->get('/up');

    expect($response->headers->get('Permissions-Policy'))
        ->toContain('camera=()')
        ->toContain('microphone=()')
        ->toContain('geolocation=()');
});

it('does not add HSTS in local environment', function () {
    $response = $this->get('/up');

    expect($response->headers->has('Strict-Transport-Security'))->toBeFalse();
});

it('adds security headers to API responses', function () {
    $response = $this->getJson('/api/v1/urls');

    $response->assertHeader('X-Frame-Options', 'DENY');
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
});

it('adds security headers to 404 responses', function () {
    $response = $this->get('/this-does-not-exist');

    $response->assertStatus(404);
    $response->assertHeader('X-Frame-Options', 'DENY');
});
