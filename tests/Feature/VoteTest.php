<?php

namespace Tests\Feature;

use App\Models\Poll;
use App\Models\PollOption;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Send the voter_token cookie as plaintext so a test can pin one
        // browser identity across requests.
        $this->withoutMiddleware(EncryptCookies::class);
    }

    public function test_a_visitor_can_cast_a_vote(): void
    {
        $poll = Poll::factory()->create();
        $option = PollOption::factory()->for($poll)->create();

        $this->withUnencryptedCookie('voter_token', 'voter-1')
            ->post("/polls/{$poll->slug}/vote", ['poll_option_id' => $option->id])
            ->assertRedirect();

        $this->assertDatabaseHas('votes', [
            'poll_id' => $poll->id,
            'poll_option_id' => $option->id,
            'voter_token' => 'voter-1',
        ]);
    }

    public function test_a_browser_can_only_vote_once_per_poll(): void
    {
        $poll = Poll::factory()->create();
        $first = PollOption::factory()->for($poll)->create();
        $second = PollOption::factory()->for($poll)->create();

        $this->withUnencryptedCookie('voter_token', 'voter-1')
            ->post("/polls/{$poll->slug}/vote", ['poll_option_id' => $first->id])
            ->assertRedirect();

        // Same browser, different option — must not create a second vote.
        $this->withUnencryptedCookie('voter_token', 'voter-1')
            ->post("/polls/{$poll->slug}/vote", ['poll_option_id' => $second->id])
            ->assertRedirect();

        $this->assertDatabaseCount('votes', 1);
        $this->assertDatabaseHas('votes', [
            'poll_id' => $poll->id,
            'poll_option_id' => $first->id,
        ]);
    }

    public function test_an_option_must_belong_to_the_poll(): void
    {
        $poll = Poll::factory()->create();
        PollOption::factory()->for($poll)->create();
        $foreignOption = PollOption::factory()->create(); // belongs to another poll

        $this->withUnencryptedCookie('voter_token', 'voter-1')
            ->post("/polls/{$poll->slug}/vote", ['poll_option_id' => $foreignOption->id])
            ->assertSessionHasErrors('poll_option_id');

        $this->assertDatabaseCount('votes', 0);
    }

    public function test_votes_are_rate_limited_per_ip(): void
    {
        $poll = Poll::factory()->create();
        $option = PollOption::factory()->for($poll)->create();

        // The 'votes' limiter allows 20 per minute per IP.
        for ($i = 0; $i < 20; $i++) {
            $this->withUnencryptedCookie('voter_token', "voter-$i")
                ->post("/polls/{$poll->slug}/vote", ['poll_option_id' => $option->id])
                ->assertRedirect();
        }

        $this->withUnencryptedCookie('voter_token', 'voter-over')
            ->post("/polls/{$poll->slug}/vote", ['poll_option_id' => $option->id])
            ->assertStatus(429);
    }
}
