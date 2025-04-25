<?php

namespace App\Http\Controllers;

use App\Http\Middleware\EnsureVoterToken;
use App\Http\Requests\StorePollRequest;
use App\Models\Poll;
use App\Models\PollOption;
use App\Support\Geolocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PollController extends Controller
{
    /**
     * List polls: closest to the visitor first, or newest first if we can't
     * place them. Each poll carries its city and (when located) its distance.
     */
    public function index(Request $request, Geolocation $geo): Response
    {
        $here = $geo->locate($request->ip());

        $polls = Poll::query()
            ->withCount('votes')
            ->latest()
            ->get()
            ->map(function (Poll $poll) use ($geo, $here): array {
                $distance = ($here && $poll->latitude !== null && $poll->longitude !== null)
                    ? (int) round($geo->distanceMiles($here['latitude'], $here['longitude'], $poll->latitude, $poll->longitude))
                    : null;

                return [
                    'slug' => $poll->slug,
                    'question' => $poll->question,
                    'votes_count' => $poll->votes_count,
                    'is_closed' => $poll->isClosed(),
                    'city' => $poll->city,
                    'distance_miles' => $distance,
                ];
            });

        if ($here) {
            // Closest first; polls without coordinates sink to the bottom while
            // keeping their newest-first order among themselves.
            $polls = $polls->sortBy(fn (array $poll): float => $poll['distance_miles'] ?? INF)->values();
        }

        return Inertia::render('Polls/Index', [
            'near' => $here['city'] ?? null,
            'polls' => $polls,
        ]);
    }

    /**
     * Show the poll-creation form.
     */
    public function create(): Response
    {
        return Inertia::render('Polls/Create');
    }

    /**
     * Persist a new poll and its options.
     */
    public function store(StorePollRequest $request, Geolocation $geo): RedirectResponse
    {
        $data = $request->validated();

        // Resolve before opening the transaction so a slow lookup never holds a
        // write lock; a failed/disabled lookup just leaves the poll unplaced.
        $location = $geo->locate($request->ip());

        $poll = DB::transaction(function () use ($data, $location): Poll {
            $poll = Poll::create([
                'question' => $data['question'],
                'slug' => $this->uniqueSlug($data['question']),
                'city' => $location['city'] ?? null,
                'region' => $location['region'] ?? null,
                'country_code' => $location['country_code'] ?? null,
                'latitude' => $location['latitude'] ?? null,
                'longitude' => $location['longitude'] ?? null,
            ]);

            foreach (array_values($data['options']) as $position => $label) {
                $poll->options()->create([
                    'label' => $label,
                    'position' => $position,
                ]);
            }

            return $poll;
        });

        return redirect()
            ->route('polls.show', $poll)
            ->with('success', 'Poll created — share the link to gather votes.');
    }

    /**
     * Show a single poll with its current results and this voter's status.
     */
    public function show(Request $request, Poll $poll): Response
    {
        $poll->load(['options' => fn ($query) => $query->withCount('votes')]);

        $token = EnsureVoterToken::current($request);
        $existingVote = $poll->votes()->where('voter_token', $token)->first();

        return Inertia::render('Polls/Show', [
            'poll' => [
                'slug' => $poll->slug,
                'question' => $poll->question,
                'is_closed' => $poll->isClosed(),
                'city' => $poll->city,
                'total_votes' => (int) $poll->options->sum('votes_count'),
                'options' => $poll->options->map(fn (PollOption $option): array => [
                    'id' => $option->id,
                    'label' => $option->label,
                    'votes_count' => $option->votes_count,
                ]),
            ],
            'hasVoted' => $existingVote !== null,
            'votedOptionId' => $existingVote?->poll_option_id,
        ]);
    }

    /**
     * Build a URL-safe, collision-free slug from the question text.
     */
    private function uniqueSlug(string $question): string
    {
        $base = Str::slug(Str::words($question, 8, '')) ?: 'poll';
        $slug = $base;
        $suffix = 2;

        while (Poll::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
