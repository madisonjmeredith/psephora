import { Link, usePage } from '@inertiajs/react';
import Button from './Button';

function Wordmark() {
    return (
        <Link
            href="/"
            className="group inline-flex items-center gap-[11px] focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500 focus-visible:ring-offset-2 focus-visible:ring-offset-cream-100"
        >
            <span
                aria-hidden="true"
                className="inline-block h-[11px] w-[11px] rounded-full bg-teal-500 transition-transform duration-200 group-hover:-translate-y-0.5"
            />
            <span className="font-display text-xl font-extrabold tracking-[0.2em] text-teal-900">
                PSEPHORA
            </span>
        </Link>
    );
}

function Flash({ tone, children }) {
    const tones = {
        success: 'border-teal-500/40 bg-teal-050 text-teal-700',
        error: 'border-ochre/40 bg-yellow-100 text-ochre',
    };

    return (
        <div
            role="status"
            className={`mb-8 rounded-[14px] border-[1.5px] px-4 py-3 text-sm font-medium ${tones[tone]}`}
        >
            {children}
        </div>
    );
}

export default function Layout({ children }) {
    const { flash } = usePage().props;

    return (
        <div className="flex min-h-full flex-col">
            <header className="border-b border-cream-300">
                <div className="grid grid-cols-[1fr_auto_1fr] items-center px-6 py-6 sm:px-10">
                    <span aria-hidden="true" />
                    <Wordmark />
                    <div className="justify-self-end">
                        <Button href="/polls/create" size="sm">
                            Start a poll
                        </Button>
                    </div>
                </div>
            </header>

            <main className="mx-auto w-full max-w-[640px] flex-1 px-6 pb-24 pt-16 sm:px-10 sm:pt-[72px]">
                {flash?.success && <Flash tone="success">{flash.success}</Flash>}
                {flash?.error && <Flash tone="error">{flash.error}</Flash>}
                {children}
            </main>

            <footer className="border-t border-cream-300">
                <div className="mx-auto w-full max-w-[640px] px-6 py-10 sm:px-10">
                    <p className="text-base leading-relaxed text-ink-400">
                        <em className="italic font-medium text-ink-600">psephora</em> takes its name
                        from the <em className="italic font-medium text-ink-600">psephos</em>: the
                        small pebble ancient Athenians dropped into an urn to cast a vote. No account
                        needed; just pick a side.
                    </p>
                </div>
            </footer>
        </div>
    );
}
