<?php

// tests/Feature/Api/AuthTest.php

use App\Models\User;

it('registers a new user and returns a token', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'user' => ['id', 'name', 'email'],
            'token',
        ])
        ->assertJsonPath('user.email', 'jane@example.com');

    expect(User::where('email', 'jane@example.com')->exists())->toBeTrue();
});

it('rejects registration with an existing email', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->postJson('/api/v1/auth/register', [
        'name' => 'Test',
        'email' => 'taken@example.com',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});

it('rejects registration with mismatched passwords', function () {
    $this->postJson('/api/v1/auth/register', [
        'name' => 'Test',
        'email' => 'x@example.com',
        'password' => 'secret123',
        'password_confirmation' => 'different',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('password');
});

it('logs in an existing user', function () {
    User::factory()->create([
        'email' => 'jane@example.com',
        'password' => bcrypt('secret123'),
    ]);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'jane@example.com',
        'password' => 'secret123',
    ])->assertOk()
        ->assertJsonStructure(['user', 'token']);
});

it('rejects login with wrong credentials', function () {
    User::factory()->create([
        'email' => 'jane@example.com',
        'password' => bcrypt('secret123'),
    ]);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'jane@example.com',
        'password' => 'wrong',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});

it('returns the authenticated user via /me', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/auth/me')
        ->assertOk()
        ->assertJsonPath('id', $user->id)
        ->assertJsonPath('email', $user->email);
});

it('revokes the current token on logout', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/auth/logout')
        ->assertOk();

    expect($user->tokens()->count())->toBe(0);
});

it('rejects unauthenticated access to /me', function () {
    $this->getJson('/api/v1/auth/me')
        ->assertUnauthorized();
});
