<?php

namespace Database\Factories;

use App\Models\Poll;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Poll>
 */
class PollFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $question = rtrim(fake()->sentence(), '.').'?';

        return [
            'question' => $question,
            'slug' => Str::slug(Str::words($question, 6, '')).'-'.Str::lower(Str::random(6)),
            'closes_at' => null,
        ];
    }

    /**
     * Place this poll in a random real city (city, region, country, coords).
     */
    public function located(): static
    {
        return $this->state(fn (): array => fake()->randomElement([
            ['city' => 'Denver', 'region' => 'Colorado', 'country_code' => 'US', 'latitude' => 39.7392, 'longitude' => -104.9903],
            ['city' => 'New York', 'region' => 'New York', 'country_code' => 'US', 'latitude' => 40.7128, 'longitude' => -74.0060],
            ['city' => 'London', 'region' => 'England', 'country_code' => 'GB', 'latitude' => 51.5074, 'longitude' => -0.1278],
            ['city' => 'Tokyo', 'region' => 'Tokyo', 'country_code' => 'JP', 'latitude' => 35.6762, 'longitude' => 139.6503],
            ['city' => 'Sydney', 'region' => 'New South Wales', 'country_code' => 'AU', 'latitude' => -33.8688, 'longitude' => 151.2093],
        ]));
    }

    /**
     * Place this poll at explicit coordinates — for deterministic distance tests.
     */
    public function at(string $city, float $latitude, float $longitude): static
    {
        return $this->state(fn (): array => [
            'city' => $city,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);
    }
}
