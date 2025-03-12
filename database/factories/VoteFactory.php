<?php

namespace Database\Factories;

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Vote>
 */
class VoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'poll_id' => Poll::factory(),
            'poll_option_id' => PollOption::factory(),
            'voter_token' => (string) Str::uuid(),
            'ip_address' => fake()->ipv4(),
        ];
    }

    /**
     * Cast this vote for a specific option, keeping poll_id consistent.
     */
    public function forOption(PollOption $option): static
    {
        return $this->state(fn (): array => [
            'poll_id' => $option->poll_id,
            'poll_option_id' => $option->id,
        ]);
    }
}
