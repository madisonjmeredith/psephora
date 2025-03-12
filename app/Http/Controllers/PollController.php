<?php

namespace App\Http\Controllers;

use App\Http\Middleware\EnsureVoterToken;
use App\Http\Requests\StorePollRequest;
use App\Models\Poll;
use App\Models\PollOption;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PollController extends Controller
{
    /**
     * List every poll, newest first, with its running vote total.
     */
    public function index(): Response
    {
        $polls = Poll::query()
            ->withCount('votes')
            ->latest()
            ->get()
            ->map(fn (Poll $poll): array => [
                'slug' => $poll->slug,
                'question' => $poll->question,
                'votes_count' => $poll->votes_count,
                'is_closed' => $poll->isClosed(),
            ]);

        return Inertia::render('Polls/Index', [
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
    public function store(StorePollRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $poll = DB::transaction(function () use ($data): Poll {
            $poll = Poll::create([
                'question' => $data['question'],
                'slug' => $this->uniqueSlug($data['question']),
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
