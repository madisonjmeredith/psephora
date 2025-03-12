import { Head, Link } from '@inertiajs/react';
import Layout from '../../components/Layout';

function pebbleCount(n) {
    return `${n.toLocaleString()} ${n === 1 ? 'pebble' : 'pebbles'}`;
}

function PollCard({ poll }) {
    return (
        <li>
            <Link
                href={`/polls/${poll.slug}`}
                className="group block rounded-xl border border-line bg-white px-6 py-5 transition-all duration-200 hover:border-verdigris/50 hover:shadow-[0_1px_0_theme(colors.verdigris)] focus:outline-none focus-visible:ring-2 focus-visible:ring-verdigris focus-visible:ring-offset-2 focus-visible:ring-offset-marble"
            >
                <h3 className="font-serif text-xl leading-snug text-ink transition-colors group-hover:text-verdigris-deep">
                    {poll.question}
                </h3>
                <div className="mt-3 flex items-center gap-3 font-mono text-xs uppercase tracking-wide text-stone">
                    <span>{pebbleCount(poll.votes_count)} cast</span>
                    {poll.is_closed && (
                        <span className="rounded-full bg-marble-deep px-2 py-0.5 text-ink-soft">
                            closed
                        </span>
                    )}
                    <span className="ml-auto text-verdigris opacity-0 transition-opacity group-hover:opacity-100">
                        view &rarr;
                    </span>
                </div>
            </Link>
        </li>
    );
}

export default function Index({ polls }) {
    return (
        <Layout>
            <Head title="Polls" />

            <section className="mb-14">
                <p className="mb-4 font-mono text-xs uppercase tracking-[0.25em] text-verdigris">
                    Open ballot
                </p>
                <h1 className="font-serif text-4xl font-semibold leading-[1.1] text-ink sm:text-5xl">
                    Settle it with a pebble.
                </h1>
                <p className="mt-5 max-w-prose text-lg leading-relaxed text-ink-soft">
                    Ask anything and let the room decide. Anyone can weigh in: no sign-up, one
                    pebble per poll.
                </p>
                <div className="mt-7">
                    <Link
                        href="/polls/create"
                        className="inline-flex items-center rounded-full bg-verdigris px-5 py-2.5 text-sm font-medium text-white transition-colors duration-200 hover:bg-verdigris-deep focus:outline-none focus-visible:ring-2 focus-visible:ring-verdigris focus-visible:ring-offset-2 focus-visible:ring-offset-marble"
                    >
                        Start a poll
                    </Link>
                </div>
            </section>

            <section>
                <h2 className="mb-5 flex items-baseline justify-between border-b border-line pb-3">
                    <span className="font-mono text-xs uppercase tracking-[0.2em] text-ink-soft">
                        Recent polls
                    </span>
                    <span className="font-mono text-xs tabular-nums text-stone">
                        {polls.length}
                    </span>
                </h2>

                {polls.length === 0 ? (
                    <div className="rounded-xl border border-dashed border-line bg-white/60 px-6 py-14 text-center">
                        <p className="font-serif text-xl text-ink">No polls yet.</p>
                        <p className="mt-2 text-ink-soft">Be the first to put a question to the room.</p>
                        <Link
                            href="/polls/create"
                            className="mt-6 inline-flex items-center rounded-full border border-ink px-5 py-2.5 text-sm font-medium text-ink transition-colors hover:bg-ink hover:text-marble focus:outline-none focus-visible:ring-2 focus-visible:ring-verdigris focus-visible:ring-offset-2 focus-visible:ring-offset-marble"
                        >
                            Start the first poll
                        </Link>
                    </div>
                ) : (
                    <ul className="space-y-3">
                        {polls.map((poll) => (
                            <PollCard key={poll.slug} poll={poll} />
                        ))}
                    </ul>
                )}
            </section>
        </Layout>
    );
}
