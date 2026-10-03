<?php
// tests/Feature/Api/UrlShorteningTest.php

use App\Models\Url;

it('lets a guest shorten a URL', function () {
    $response = $this->postJson('/api/v1/urls', [
        'original_url' => 'https://laravel.com/docs',
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'data' => [
                'id',
                'original_url',
                'short_code',
                'short_url',
                'clicks_count',
                'is_active',
                'expires_at',
                'created_at',
                'updated_at',
            ],
        ])
        ->assertJsonPath('data.original_url', 'https://laravel.com/docs');

    expect(Url::count())->toBe(1);
    expect(Url::first()->user_id)->toBeNull();
});

it('rejects an invalid URL', function () {
    $this->postJson('/api/v1/urls', [
        'original_url' => 'not-a-url',
    ])->assertUnprocessable()
      ->assertJsonValidationErrors('original_url');
});

it('rejects a missing URL', function () {
    $this->postJson('/api/v1/urls', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('original_url');
});

it('accepts a custom alias', function () {
    $response = $this->postJson('/api/v1/urls', [
        'original_url' => 'https://anthropic.com',
        'custom_alias' => 'claude',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.custom_alias', 'claude')
        ->assertJsonPath('data.public_code', 'claude');

    expect(Url::first()->custom_alias)->toBe('claude');
});

it('rejects a reserved custom alias', function () {
    $this->postJson('/api/v1/urls', [
        'original_url' => 'https://example.com',
        'custom_alias' => 'api',
    ])->assertUnprocessable()
      ->assertJsonValidationErrors('custom_alias');
});

it('rejects a duplicate custom alias', function () {
    Url::factory()->withAlias('taken')->create();

    $this->postJson('/api/v1/urls', [
        'original_url' => 'https://example.com',
        'custom_alias' => 'taken',
    ])->assertUnprocessable()
      ->assertJsonValidationErrors('custom_alias');
});