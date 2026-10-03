<?php

// tests/Pest.php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case Bindings
|--------------------------------------------------------------------------
|
| Feature tests bind to the Laravel TestCase and refresh the DB per test.
| Unit tests bind to TestCase but don't need the DB.
|
*/

pest()
    ->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

pest()
    ->extend(TestCase::class)
    ->in('Unit');

/*
|--------------------------------------------------------------------------
| Custom Expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toBeValidUrl', function () {
    return $this->toMatch('/^https?:\/\/.+/');
});
