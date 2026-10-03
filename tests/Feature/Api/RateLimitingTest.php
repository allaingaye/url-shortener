<?php

// tests/Feature/Api/RateLimitingTest.php

use App\Models\Url;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    // Ensure a clean rate-limiter slate between tests
    RateLimiter::clear('login');
    cache()->clear();
});

it('throttles login after 5 attempts', function () {
    User::factory()->create([
        'email' => 'jane@example.com',
        'password' => bcrypt('secret123'),
    ]);

    // 5 attempts allowed
    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/api/v1/auth/login', [
            'email' => 'jane@example.com',
            'password' => 'wrong',
        ])->assertUnprocessable();
    }

    // 6th is rate-limited
    $this->postJson('/api/v1/auth/login', [
        'email' => 'jane@example.com',
        'password' => 'wrong',
    ])->assertStatus(429)
        ->assertJsonPath('message', 'Too many login attempts. Please try again later.');
});

it('throttles registration after 5 attempts', function () {
    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/api/v1/auth/register', [
            'name' => "User {$i}",
            'email' => "user{$i}@example.com",
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertCreated();
    }

    $this->postJson('/api/v1/auth/register', [
        'name' => 'Blocked',
        'email' => 'blocked@example.com',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ])->assertStatus(429);
});

it('throttles redirects after 300 hits', function () {
    Url::factory()->create(['short_code' => 'hot']);

    // 300 allowed
    for ($i = 0; $i < 300; $i++) {
        $this->get('/hot')->assertRedirect();
    }

    // 301st is throttled
    $this->get('/hot')->assertStatus(429);
})->skip('Slow test — run manually with --filter=throttles_redirects');

it('includes retry-after header on 429', function () {
    User::factory()->create(['email' => 'jane@example.com']);

    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/api/v1/auth/login', [
            'email' => 'jane@example.com',
            'password' => 'x',
        ]);
    }

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'jane@example.com',
        'password' => 'x',
    ]);

    $response->assertStatus(429);
    expect($response->headers->get('Retry-After'))->not->toBeNull();
    expect($response->headers->get('X-RateLimit-Limit'))->toBe('5');
});

it('allows different emails to have independent login limits', function () {
    User::factory()->create(['email' => 'a@example.com', 'password' => bcrypt('x')]);
    User::factory()->create(['email' => 'b@example.com', 'password' => bcrypt('x')]);

    // Fill up A's limit
    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/api/v1/auth/login', ['email' => 'a@example.com', 'password' => 'x']);
    }

    $this->postJson('/api/v1/auth/login', ['email' => 'a@example.com', 'password' => 'x'])
        ->assertStatus(429);

    // B is still fine
    $this->postJson('/api/v1/auth/login', ['email' => 'b@example.com', 'password' => 'wrong'])
        ->assertUnprocessable();
});
