<?php
// database/factories/ClickFactory.php

namespace Database\Factories;

use App\Models\Url;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClickFactory extends Factory
{
    public function definition(): array
    {
        return [
            'url_id'     => Url::factory(),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => 'Mozilla/5.0 Test',
            'referer'    => 'https://example.com/',
            'device'     => 'desktop',
            'browser'    => 'Chrome',
            'platform'   => 'Windows',
            'country'    => null,
        ];
    }
}