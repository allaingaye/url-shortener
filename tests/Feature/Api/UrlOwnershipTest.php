<?php
// tests/Feature/Api/UrlOwnershipTest.php

use App\Models\Url;
use App\Models\User;

beforeEach(function () {
    $this->jane = User::factory()->create(['email' => 'jane@example.com']);
    $this->bob  = User::factory()->create(['email' => 'bob@example.com']);
});

// ─── CREATE ──────────────────────────────────────────────

it('assigns ownership when an authenticated user creates a URL', function () {
    $response = $this->actingAs($this->jane, 'sanctum')
        ->postJson('/api/v1/urls', ['original_url' => 'https://jane.com']);

    $response->assertCreated();

    expect(Url::first()->user_id)->toBe($this->jane->id);
});

it('leaves user_id null when a guest creates a URL', function () {
    $this->postJson('/api/v1/urls', ['original_url' => 'https://guest.com'])
        ->assertCreated();

    expect(Url::first()->user_id)->toBeNull();
});

// ─── LIST ────────────────────────────────────────────────

it('lists only the authenticated user\'s URLs', function () {
    Url::factory()->ownedBy($this->jane)->count(3)->create();
    Url::factory()->ownedBy($this->bob)->count(2)->create();
    Url::factory()->count(4)->create(); // guests

    $response = $this->actingAs($this->jane, 'sanctum')
        ->getJson('/api/v1/urls');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(3);
});

it('rejects unauthenticated list requests', function () {
    $this->getJson('/api/v1/urls')->assertUnauthorized();
});

// ─── SHOW ────────────────────────────────────────────────

it('allows the owner to view their URL', function () {
    $url = Url::factory()->ownedBy($this->jane)->create();

    $this->actingAs($this->jane, 'sanctum')
        ->getJson("/api/v1/urls/{$url->id}")
        ->assertOk();
});

it('forbids another user from viewing someone else\'s URL', function () {
    $url = Url::factory()->ownedBy($this->jane)->create();

    $this->actingAs($this->bob, 'sanctum')
        ->getJson("/api/v1/urls/{$url->id}")
        ->assertForbidden();
});

// ─── UPDATE ──────────────────────────────────────────────

it('allows the owner to update their URL', function () {
    $url = Url::factory()->ownedBy($this->jane)->create();

    $this->actingAs($this->jane, 'sanctum')
        ->patchJson("/api/v1/urls/{$url->id}", ['is_active' => false])
        ->assertOk()
        ->assertJsonPath('data.is_active', false);
});

it('forbids another user from updating someone else\'s URL', function () {
    $url = Url::factory()->ownedBy($this->jane)->create();

    $this->actingAs($this->bob, 'sanctum')
        ->patchJson("/api/v1/urls/{$url->id}", ['is_active' => false])
        ->assertForbidden();
});

// ─── DELETE ──────────────────────────────────────────────

it('allows the owner to delete their URL', function () {
    $url = Url::factory()->ownedBy($this->jane)->create();

    $this->actingAs($this->jane, 'sanctum')
        ->deleteJson("/api/v1/urls/{$url->id}")
        ->assertOk();

    expect(Url::find($url->id))->toBeNull();
});

it('forbids another user from deleting someone else\'s URL', function () {
    $url = Url::factory()->ownedBy($this->jane)->create();

    $this->actingAs($this->bob, 'sanctum')
        ->deleteJson("/api/v1/urls/{$url->id}")
        ->assertForbidden();

    expect(Url::find($url->id))->not->toBeNull();
});