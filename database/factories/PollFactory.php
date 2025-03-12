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
}
