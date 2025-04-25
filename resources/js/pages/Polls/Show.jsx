import { Head, Link, useForm } from '@inertiajs/react';
import { useState } from 'react';
import Layout from '../../components/Layout';
import ResultBar from '../../components/ResultBar';

function pebbleCount(n) {
    return `${n.toLocaleString()} ${n === 1 ? 'pebble' : 'pebbles'}`;
}

function VoteForm({ poll }) {
    const { data, setData, post, processing, errors } = useForm({ poll_option_id: null });

    function submit(e) {
        e.preventDefault();
        post(`/polls/${poll.slug}/vote`, { preserveScroll: true });
    }

    return (
        <form onSubmit={submit}>
            <ul className="space-y-3" role="radiogroup" aria-label="Poll options">
                {poll.options.map((option) => {
                    const selected = data.poll_option_id === option.id;
                    return (
                        <li key={option.id}>
                            <label
                                className={`flex cursor-pointer items-center gap-4 rounded-xl border px-5 py-4 transition-all duration-150 ${
                                    selected
                                        ? 'border-verdigris bg-verdigris-wash ring-1 ring-verdigris'
                                        : 'border-line bg-white hover:border-verdigris/50'
                                }`}
                            >
                                <input
                                    type="radio"
                                    name="poll_option_id"
                                    value={option.id}
                                    checked={selected}
                                    onChange={() => setData('poll_option_id', option.id)}
                                    className="sr-only"
                                />
                                <span
                                    aria-hidden="true"
                                    className={`flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2 transition-colors ${
                                        selected ? 'border-verdigris' : 'border-stone/50'
                                    }`}
                                >
                                    <span
                                        className={`h-2.5 w-2.5 rounded-full bg-verdigris transition-transform duration-150 ${
                                            selected ? 'scale-100' : 'scale-0'
                                        }`}
                                    />
                                </span>
                                <span className="font-serif text-lg text-ink">{option.label}</span>
                            </label>
                        </li>
                    );
                })}
            </ul>

            {errors.poll_option_id && (
                <p className="mt-3 text-sm text-ochre">{errors.poll_option_id}</p>
            )}

            <button
                type="submit"
                disabled={processing || data.poll_option_id === null}
                className="mt-6 rounded-full bg-verdigris px-6 py-2.5 text-sm font-medium text-white transition-colors hover:bg-verdigris-deep disabled:opacity-40 focus:outline-none focus-visible:ring-2 focus-visible:ring-verdigris focus-visible:ring-offset-2 focus-visible:ring-offset-marble"
            >
                {processing ? 'Casting…' : 'Cast your pebble'}
            </button>
        </form>
    );
}

function Results({ poll, votedOptionId }) {
    const total = poll.total_votes;
    const max = poll.options.reduce((m, o) => Math.max(m, o.votes_count), 0);

    return (
        <div>
            <ul className="space-y-5">
                {poll.options.map((option) => (
                    <ResultBar
                        key={option.id}
                        label={option.label}
                        count={option.votes_count}
                        percent={total > 0 ? Math.round((option.votes_count / total) * 100) : 0}
                        isWinner={total > 0 && option.votes_count === max}
                        isYours={option.id === votedOptionId}
                    />
                ))}
            </ul>
            <p className="mt-6 font-mono text-xs uppercase tracking-wide text-stone">
                {pebbleCount(total)} cast
            </p>
        </div>
    );
}

export default function Show({ poll, hasVoted, votedOptionId }) {
    const [reveal, setReveal] = useState(false);
    const showResults = hasVoted || poll.is_closed || reveal;

    const [copied, setCopied] = useState(false);
    function copyLink() {
        navigator.clipboard?.writeText(window.location.href).then(() => {
            setCopied(true);
            window.setTimeout(() => setCopied(false), 2000);
        });
    }

    return (
        <Layout>
            <Head title={poll.question} />

            <div className="mb-8">
                <Link
                    href="/"
                    className="font-mono text-xs uppercase tracking-wide text-stone transition-colors hover:text-verdigris"
                >
                    &larr; All polls
                </Link>
                <h1 className="mt-4 font-serif text-3xl font-semibold leading-tight text-ink sm:text-4xl">
                    {poll.question}
                </h1>
                <div className="mt-3 flex items-center gap-3 font-mono text-xs uppercase tracking-wide text-stone">
                    {poll.city && <span>Asked from {poll.city}</span>}
                    {hasVoted && !poll.is_closed && (
                        <span className="text-verdigris-deep">You’ve cast your pebble</span>
                    )}
                    {poll.is_closed && (
                        <span className="rounded-full bg-marble-deep px-2 py-0.5 text-ink-soft">
                            closed
                        </span>
                    )}
                </div>
            </div>

            <div className="rounded-2xl border border-line bg-white p-6 sm:p-8">
                {showResults ? (
                    <Results poll={poll} votedOptionId={votedOptionId} />
                ) : (
                    <VoteForm poll={poll} />
                )}
            </div>

            <div className="mt-6 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm">
                {!showResults && (
                    <button
                        type="button"
                        onClick={() => setReveal(true)}
                        className="text-stone underline-offset-4 transition-colors hover:text-ink hover:underline"
                    >
                        Just show me the results
                    </button>
                )}
                <button
                    type="button"
                    onClick={copyLink}
                    className="ml-auto inline-flex items-center gap-2 text-stone transition-colors hover:text-verdigris"
                >
                    {copied ? 'Link copied' : 'Copy share link'}
                </button>
            </div>
        </Layout>
    );
}
