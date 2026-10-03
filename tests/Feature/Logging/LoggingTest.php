<?php

// tests/Feature/Logging/LoggingTest.php

use App\Models\Url;
use App\Models\User;
use App\Services\AnalyticsService;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    // Clean log files before each test
    foreach (glob(storage_path('logs/*.log')) as $file) {
        @unlink($file);
    }
});

function readLog(string $channel): string
{
    $files = glob(storage_path("logs/{$channel}-*.log"));

    return empty($files) ? '' : File::get($files[0]);
}

it('logs successful login to the auth channel', function () {
    User::factory()->create([
        'email' => 'jane@example.com',
        'password' => bcrypt('secret123'),
    ]);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'jane@example.com',
        'password' => 'secret123',
    ])->assertOk();

    expect(readLog('auth'))
        ->toContain('User logged in')
        ->toContain('jane@example.com')
        ->toContain('testing.INFO');
});

it('logs failed login to the auth channel', function () {
    $this->postJson('/api/v1/auth/login', [
        'email' => 'nonexistent@example.com',
        'password' => 'wrong',
    ])->assertUnprocessable();

    expect(readLog('auth'))
        ->toContain('Failed login attempt')
        ->toContain('nonexistent@example.com')
        ->toContain('testing.WARNING');
});

it('logs user registration to the auth channel', function () {
    $this->postJson('/api/v1/auth/register', [
        'name' => 'Jane',
        'email' => 'jane@example.com',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ])->assertCreated();

    expect(readLog('auth'))
        ->toContain('User registered')
        ->toContain('jane@example.com');
});

it('logs logout to the auth channel', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/auth/logout')
        ->assertOk();

    expect(readLog('auth'))
        ->toContain('User logged out')
        ->toContain("user_id\":{$user->id}");
});

it('logs recorded clicks to the clicks channel', function () {
    $url = Url::factory()->create(['short_code' => 'logtest']);
    app(AnalyticsService::class)->record(
        url: $url,
        ip: '127.0.0.1',
        userAgent: 'Mozilla/5.0 (Windows NT 10.0) Chrome/120.0',
        referer: 'https://google.com/',
    );

    expect(readLog('clicks'))
        ->toContain('Click recorded')
        ->toContain('logtest')
        ->toContain('Chrome');
});

it('logs errors to the errors channel', function () {
    try {
        throw new RuntimeException('Test exception for logging');
    } catch (Throwable $e) {
        app(ExceptionHandler::class)->report($e);
    }

    expect(readLog('errors'))
        ->toContain('Test exception for logging')
        ->toContain('RuntimeException');
});
