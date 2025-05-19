import { Head, Link, useForm } from '@inertiajs/react';
import { useState } from 'react';
import Layout from '../../components/Layout';
import Button from '../../components/Button';
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
            <ul className="flex flex-col gap-3.5" role="radiogroup" aria-label="Poll options">
                {poll.options.map((option) => {
                    const selected = data.poll_option_id === option.id;
                    return (
                        <li key={option.id}>
                            <label
                                className={`flex cursor-pointer items-center gap-4 rounded-[14px] border-[1.5px] border-teal-900 px-[22px] py-[18px] transition-[background-color,box-shadow] duration-150 ${
                                    selected
                                        ? 'bg-teal-050 ring-[1.5px] ring-teal-500'
                                        : 'bg-white hover:bg-cream-050'
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
                                    className={`flex h-6 w-6 shrink-0 items-center justify-center rounded-full border-[1.5px] transition-colors ${
                                        selected ? 'border-teal-500 bg-teal-500' : 'border-teal-900'
                                    }`}
                                >
                                    <span
                                        className={`h-[9px] w-[9px] rounded-full bg-white transition-opacity duration-150 ${
                                            selected ? 'opacity-100' : 'opacity-0'
                                        }`}
                                    />
                                </span>
                                <span className="font-display text-[19px] font-medium text-teal-900">
                                    {option.label}
                                </span>
                            </label>
                        </li>
                    );
                })}
            </ul>

            {errors.poll_option_id && (
                <p className="mt-3 text-sm text-ochre">{errors.poll_option_id}</p>
            )}

            <div className="mt-[26px]">
                <Button
                    type="submit"
                    disabled={processing || data.poll_option_id === null}
                >
                    {processing ? 'Casting…' : 'Cast your pebble'}
                </Button>
            </div>
        </form>
    );
}

function Results({ poll, votedOptionId }) {
    const total = poll.total_votes;
    const max = poll.options.reduce((m, o) => Math.max(m, o.votes_count), 0);

    return (
        <div>
            <ul className="flex flex-col gap-[18px]">
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
            <p className="mt-7 font-numeric text-[12.5px] font-semibold uppercase tracking-[0.14em] text-ink-400">
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
                    className="inline-flex items-center gap-2 font-numeric text-[13px] font-semibold uppercase tracking-[0.14em] text-ink-400 transition-colors hover:text-teal-700"
                >
                    <svg
                        width="15"
                        height="15"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        strokeWidth="2.2"
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        aria-hidden="true"
                    >
                        <path d="M19 12H5" />
                        <path d="M12 19l-7-7 7-7" />
                    </svg>
                    All polls
                </Link>
                <h1 className="mt-5 font-display text-[40px] font-extrabold leading-[1.08] tracking-[-0.02em] text-teal-900 sm:text-[46px]">
                    {poll.question}
                </h1>
                <div className="mt-4 flex flex-wrap items-center gap-3 font-numeric text-[13px] font-semibold uppercase tracking-[0.14em] text-ink-400">
                    {poll.city && <span>Asked from {poll.city}</span>}
                    {hasVoted && !poll.is_closed && (
                        <span className="text-teal-700">You’ve cast your pebble</span>
                    )}
                    {poll.is_closed && (
                        <span className="rounded-full border border-cream-300 px-2 py-0.5 normal-case tracking-normal text-teal-700">
                            Closed
                        </span>
                    )}
                </div>
            </div>

            <div className="rounded-[18px] border-[1.5px] border-teal-900 bg-white p-7 shadow-stack sm:p-10">
                {showResults ? (
                    <Results poll={poll} votedOptionId={votedOptionId} />
                ) : (
                    <VoteForm poll={poll} />
                )}
            </div>

            <div className="mt-[22px] flex items-center gap-6 text-[16px]">
                {!showResults && (
                    <button
                        type="button"
                        onClick={() => setReveal(true)}
                        className="text-ink-600 transition-colors hover:text-teal-700"
                    >
                        Just show me the results
                    </button>
                )}
                <button
                    type="button"
                    onClick={copyLink}
                    className="ml-auto text-ink-600 transition-colors hover:text-teal-700"
                >
                    {copied ? 'Link copied' : 'Copy share link'}
                </button>
            </div>
        </Layout>
    );
}
