import { Link, usePage } from '@inertiajs/react';

function Wordmark() {
    return (
        <Link
            href="/"
            className="group inline-flex items-baseline gap-2 focus:outline-none focus-visible:ring-2 focus-visible:ring-verdigris focus-visible:ring-offset-2 focus-visible:ring-offset-marble"
        >
            <span
                aria-hidden="true"
                className="relative top-[-1px] inline-block h-2.5 w-2.5 rounded-full bg-verdigris transition-transform duration-200 group-hover:-translate-y-0.5"
            />
            <span className="font-serif text-xl font-semibold tracking-[0.2em] text-ink">
                PSEPHORA
            </span>
        </Link>
    );
}

function Flash({ tone, children }) {
    const tones = {
        success: 'border-verdigris/30 bg-verdigris-wash text-verdigris-deep',
        error: 'border-ochre/40 bg-[#f7efdc] text-ochre',
    };

    return (
        <div
            role="status"
            className={`mb-8 rounded-lg border px-4 py-3 text-sm font-medium ${tones[tone]}`}
        >
            {children}
        </div>
    );
}

export default function Layout({ children, wide = false }) {
    const { flash } = usePage().props;

    return (
        <div className="flex min-h-full flex-col">
            <header className="border-b border-line/70">
                <div className="mx-auto flex w-full max-w-4xl items-center justify-between px-6 py-5">
                    <Wordmark />
                    <Link
                        href="/polls/create"
                        className="rounded-full bg-ink px-4 py-2 text-sm font-medium text-marble transition-colors duration-200 hover:bg-verdigris-deep focus:outline-none focus-visible:ring-2 focus-visible:ring-verdigris focus-visible:ring-offset-2 focus-visible:ring-offset-marble"
                    >
                        Start a poll
                    </Link>
                </div>
            </header>

            <main className="mx-auto w-full flex-1 px-6 py-12" style={{ maxWidth: wide ? '56rem' : '42rem' }}>
                {flash?.success && <Flash tone="success">{flash.success}</Flash>}
                {flash?.error && <Flash tone="error">{flash.error}</Flash>}
                {children}
            </main>

            <footer className="border-t border-line/70">
                <div className="mx-auto w-full max-w-4xl px-6 py-8">
                    <p className="max-w-prose text-sm leading-relaxed text-stone">
                        <span className="font-serif italic text-ink-soft">psephora</span> takes its
                        name from the <span className="font-serif italic">psephos</span>: the small
                        pebble ancient Athenians dropped into an urn to cast a vote. No account
                        needed; just pick a side.
                    </p>
                </div>
            </footer>
        </div>
    );
}
