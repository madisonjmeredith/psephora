import { Head, Link } from '@inertiajs/react';
import Layout from '../../components/Layout';
import Button from '../../components/Button';

function pebbleCount(n) {
    return `${n.toLocaleString()} ${n === 1 ? 'pebble' : 'pebbles'}`;
}

function PollCard({ poll }) {
    const meta = [`${pebbleCount(poll.votes_count)} cast`];
    if (poll.location) {
        meta.push(
            poll.distance_miles != null
                ? `${poll.location} · ${poll.distance_miles} mi`
                : poll.location,
        );
    }

    return (
        <Link
            href={`/polls/${poll.slug}`}
            className="block rounded-[18px] border-[1.5px] border-teal-900 bg-white px-7 py-6 transition-[transform,box-shadow] duration-150 hover:-translate-y-0.5 hover:shadow-stack focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500 focus-visible:ring-offset-2 focus-visible:ring-offset-cream-100 motion-reduce:transition-none"
        >
            <h3 className="font-display text-[22px] font-semibold tracking-[-0.01em] text-teal-900">
                {poll.question}
            </h3>
            <div className="mt-3 flex items-center gap-3 font-numeric text-[12.5px] uppercase tracking-[0.1em] text-ink-400">
                <span>{meta.join('  ·  ')}</span>
                {poll.is_closed && (
                    <span className="rounded-full border border-cream-300 px-2 py-0.5 text-[11px] normal-case tracking-normal text-teal-700">
                        Closed
                    </span>
                )}
            </div>
        </Link>
    );
}

export default function Index({ polls, near }) {
    return (
        <Layout>
            <Head title="Polls" />

            <section className="mb-16">
                <p className="mb-5 font-numeric text-[13px] font-semibold uppercase tracking-[0.18em] text-teal-500">
                    Open ballot
                </p>
                <h1 className="font-display text-[40px] font-extrabold leading-[1.05] tracking-[-0.02em] text-teal-900 sm:text-[54px]">
                    Settle it with a pebble.
                </h1>
                <p className="mt-6 max-w-[30ch] text-[19px] leading-normal text-ink-600">
                    Ask anything and let the room decide. Anyone can weigh in: no sign-up, one
                    pebble per poll.
                </p>
                <div className="mt-8">
                    <Button href="/polls/create" size="lg">
                        Start a poll
                    </Button>
                </div>
            </section>

            <section>
                <h2 className="mb-5 flex items-center justify-between border-b border-cream-300 pb-3.5">
                    <span className="font-numeric text-[13px] font-semibold uppercase tracking-[0.16em] text-ink-400">
                        {near ? `Polls near ${near}` : 'Recent polls'}
                    </span>
                    <span className="font-numeric text-[13px] font-semibold text-ink-400">
                        {polls.length}
                    </span>
                </h2>

                {polls.length === 0 ? (
                    <div className="rounded-[18px] border-[1.5px] border-dashed border-teal-900 bg-white px-6 py-14 text-center">
                        <p className="font-display text-xl font-semibold text-teal-900">
                            No polls yet.
                        </p>
                        <p className="mt-2 text-ink-600">
                            Be the first to put a question to the room.
                        </p>
                        <div className="mt-6">
                            <Button href="/polls/create">Start the first poll</Button>
                        </div>
                    </div>
                ) : (
                    <div className="flex flex-col gap-[18px]">
                        {polls.map((poll) => (
                            <PollCard key={poll.slug} poll={poll} />
                        ))}
                    </div>
                )}
            </section>
        </Layout>
    );
}
