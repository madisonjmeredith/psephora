<?php

namespace Tests\Feature;

use App\Models\Poll;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class PollTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_index_lists_polls(): void
    {
        Poll::factory()->create([
            'question' => 'Best pizza topping?',
            'slug' => 'best-pizza-topping',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Polls/Index')
                ->has('polls', 1)
            );
    }

    public function test_a_poll_can_be_created_with_its_options(): void
    {
        $response = $this->post('/polls', [
            'question' => 'What should we build next?',
            'options' => ['A CLI', 'A mobile app', 'An API'],
        ]);

        $poll = Poll::first();

        $this->assertNotNull($poll);
        $response->assertRedirect("/polls/{$poll->slug}");
        $this->assertDatabaseCount('poll_options', 3);
        $this->assertSame(
            ['A CLI', 'A mobile app', 'An API'],
            $poll->options->pluck('label')->all(),
        );
    }

    public function test_a_poll_requires_at_least_two_options(): void
    {
        $this->post('/polls', [
            'question' => 'Is one option enough?',
            'options' => ['Just this one'],
        ])->assertSessionHasErrors('options');

        $this->assertDatabaseCount('polls', 0);
    }

    public function test_blank_option_rows_are_dropped_before_validation(): void
    {
        $this->post('/polls', [
            'question' => 'Coffee or tea?',
            'options' => ['Coffee', 'Tea', '', '   '],
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('poll_options', 2);
    }

    public function test_duplicate_options_are_rejected(): void
    {
        $this->post('/polls', [
            'question' => 'Pick a color',
            'options' => ['Teal', 'teal'],
        ])->assertSessionHasErrors('options.1');

        $this->assertDatabaseCount('polls', 0);
    }
}
