<?php

namespace Tests\Feature;

use App\Models\Poll;
use App\Support\Geolocation;
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

    public function test_creating_a_poll_captures_the_creators_location(): void
    {
        $this->fakeGeolocation([
            'city' => 'Denver', 'region' => 'Colorado', 'country_code' => 'US',
            'latitude' => 39.7392, 'longitude' => -104.9903,
        ]);

        $this->post('/polls', [
            'question' => 'Where should the retreat be?',
            'options' => ['Mountains', 'Beach'],
        ])->assertRedirect();

        $poll = Poll::firstWhere('question', 'Where should the retreat be?');

        $this->assertSame('Denver', $poll->city);
        $this->assertSame('US', $poll->country_code);
        $this->assertSame(39.7392, $poll->latitude);
        $this->assertSame(-104.9903, $poll->longitude);
    }

    public function test_a_poll_saves_without_a_location_when_the_lookup_fails(): void
    {
        $this->fakeGeolocation(null);

        $this->post('/polls', [
            'question' => 'Does this still work offline?',
            'options' => ['Yes', 'No'],
        ])->assertRedirect();

        $this->assertDatabaseHas('polls', [
            'question' => 'Does this still work offline?',
            'city' => null,
            'latitude' => null,
            'longitude' => null,
        ]);
    }

    public function test_index_orders_polls_by_distance_when_the_visitor_is_located(): void
    {
        // Visitor in Denver.
        $this->fakeGeolocation([
            'city' => 'Denver', 'region' => 'Colorado', 'country_code' => 'US',
            'latitude' => 39.7392, 'longitude' => -104.9903,
        ]);

        $far = Poll::factory()->at('Tokyo', 35.6762, 139.6503)->create();
        $near = Poll::factory()->at('Boulder', 40.0150, -105.2705)->create();
        $mid = Poll::factory()->at('Chicago', 41.8781, -87.6298)->create();

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Polls/Index')
                ->where('near', 'Denver')
                ->where('polls.0.slug', $near->slug)
                ->where('polls.1.slug', $mid->slug)
                ->where('polls.2.slug', $far->slug)
            );
    }

    public function test_index_falls_back_to_newest_first_when_the_visitor_is_unlocated(): void
    {
        $this->fakeGeolocation(null);

        $older = Poll::factory()->at('Tokyo', 35.6762, 139.6503)->create(['created_at' => now()->subMinute()]);
        $newer = Poll::factory()->at('Boulder', 40.0150, -105.2705)->create(['created_at' => now()]);

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Polls/Index')
                ->where('near', null)
                ->where('polls.0.slug', $newer->slug)
                ->where('polls.1.slug', $older->slug)
            );
    }

    /**
     * Swap in a Geolocation that always resolves to the given place (or null),
     * so tests never depend on a live IP lookup.
     *
     * @param  array<string, mixed>|null  $position
     */
    private function fakeGeolocation(?array $position): void
    {
        $this->app->instance(Geolocation::class, new class($position) extends Geolocation
        {
            public function __construct(private ?array $position) {}

            public function locate(?string $ip): ?array
            {
                return $this->position;
            }
        });
    }
}
