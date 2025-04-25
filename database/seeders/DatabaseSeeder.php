<?php

namespace Database\Seeders;

use App\Models\Poll;
use App\Models\Vote;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed a handful of example polls so the app has something to show.
     */
    public function run(): void
    {
        $samples = [
            [
                'question' => 'What should we name the office plant?',
                'options' => ['Fernando', 'Leaf Erikson', 'Sir Grows-a-Lot', 'Photosynthia'],
                'location' => ['city' => 'Denver', 'region' => 'Colorado', 'country_code' => 'US', 'latitude' => 39.7392, 'longitude' => -104.9903],
            ],
            [
                'question' => 'Best day for the weekly team sync?',
                'options' => ['Monday morning', 'Wednesday noon', 'Friday afternoon'],
                'location' => ['city' => 'London', 'region' => 'England', 'country_code' => 'GB', 'latitude' => 51.5074, 'longitude' => -0.1278],
            ],
            [
                'question' => 'Tabs or spaces?',
                'options' => ['Tabs', 'Spaces'],
                'location' => ['city' => 'Tokyo', 'region' => 'Tokyo', 'country_code' => 'JP', 'latitude' => 35.6762, 'longitude' => 139.6503],
            ],
        ];

        foreach ($samples as $sample) {
            $poll = Poll::factory()->create([
                'question' => $sample['question'],
                'slug' => Str::slug($sample['question']),
                ...$sample['location'],
            ]);

            $options = collect($sample['options'])->map(
                fn (string $label, int $i) => $poll->options()->create([
                    'label' => $label,
                    'position' => $i,
                ]),
            );

            // Scatter a realistic spread of pebbles across the options.
            foreach (range(1, random_int(9, 45)) as $ignored) {
                Vote::factory()
                    ->forOption($options->random())
                    ->create(['voter_token' => (string) Str::uuid()]);
            }
        }
    }
}
