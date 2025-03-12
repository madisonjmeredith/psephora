<?php

namespace App\Http\Controllers;

use App\Http\Middleware\EnsureVoterToken;
use App\Models\Poll;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VoteController extends Controller
{
    /**
     * Record one vote for the given poll.
     *
     * The chosen option must belong to this poll. One vote per browser is
     * enforced by the unique (poll_id, voter_token) index — a duplicate
     * surfaces as a QueryException, which we translate into a friendly note.
     */
    public function store(Request $request, Poll $poll): RedirectResponse
    {
        if ($poll->isClosed()) {
            return back()->with('error', 'This poll is closed to new votes.');
        }

        $validated = $request->validate([
            'poll_option_id' => [
                'required',
                Rule::exists('poll_options', 'id')->where('poll_id', $poll->id),
            ],
        ]);

        try {
            $poll->votes()->create([
                'poll_option_id' => $validated['poll_option_id'],
                'voter_token' => EnsureVoterToken::current($request),
                'ip_address' => $request->ip(),
            ]);
        } catch (QueryException) {
            return back()->with('error', 'You have already voted in this poll.');
        }

        return back()->with('success', 'Thanks for voting!');
    }
}
