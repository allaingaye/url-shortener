<?php
// tests/Feature/Public/RedirectTest.php

use App\Models\Url;

it('redirects a short code to the original URL', function () {
    $url = Url::factory()->create([
        'original_url' => 'https://laravel.com',
        'short_code'   => 'test123',
    ]);

    $this->get('/test123')
        ->assertRedirect('https://laravel.com');

    expect($url->fresh()->clicks_count)->toBe(1);
});

it('redirects a custom alias', function () {
    Url::factory()->withAlias('my-alias')->create([
        'original_url' => 'https://laravel.com',
    ]);

    $this->get('/my-alias')
        ->assertRedirect('https://laravel.com');
});

it('returns 404 for an unknown short code', function () {
    $this->get('/nope123')
        ->assertNotFound();
});

it('returns 404 for an expired URL', function () {
    Url::factory()->expired()->create(['short_code' => 'expired']);

    $this->get('/expired')->assertNotFound();
});

it('returns 404 for an inactive URL', function () {
    Url::factory()->inactive()->create(['short_code' => 'paused']);

    $this->get('/paused')->assertNotFound();
});

it('increments clicks_count on each hit', function () {
    $url = Url::factory()->create(['short_code' => 'clicks']);

    $this->get('/clicks');
    $this->get('/clicks');
    $this->get('/clicks');

    expect($url->fresh()->clicks_count)->toBe(3);
});