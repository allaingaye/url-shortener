<?php
// database/factories/UrlFactory.php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UrlFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'      => null,
            'original_url' => $this->faker->url(),
            'short_code'   => Str::random(7),
            'custom_alias' => null,
            'expires_at'   => null,
            'is_active'    => true,
            'clicks_count' => 0,
        ];
    }

    public function ownedBy(User $user): static
    {
        return $this->state(fn () => ['user_id' => $user->id]);
    }

    public function expired(): static
    {
        return $this->state(fn () => ['expires_at' => now()->subDay()]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function withAlias(string $alias): static
    {
        return $this->state(fn () => ['custom_alias' => $alias]);
    }
}