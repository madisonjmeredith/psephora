import { Head, Link, useForm } from '@inertiajs/react';
import Layout from '../../components/Layout';

const MAX_OPTIONS = 10;
const MIN_OPTIONS = 2;

export default function Create() {
    const { data, setData, post, processing, errors } = useForm({
        question: '',
        options: ['', ''],
    });

    function setOption(index, value) {
        setData(
            'options',
            data.options.map((opt, i) => (i === index ? value : opt)),
        );
    }

    function addOption() {
        if (data.options.length < MAX_OPTIONS) {
            setData('options', [...data.options, '']);
        }
    }

    function removeOption(index) {
        if (data.options.length > MIN_OPTIONS) {
            setData(
                'options',
                data.options.filter((_, i) => i !== index),
            );
        }
    }

    function submit(e) {
        e.preventDefault();
        post('/polls');
    }

    return (
        <Layout>
            <Head title="Start a poll" />

            <div className="mb-8">
                <Link
                    href="/"
                    className="font-mono text-xs uppercase tracking-wide text-stone transition-colors hover:text-verdigris"
                >
                    &larr; All polls
                </Link>
                <h1 className="mt-4 font-serif text-3xl font-semibold text-ink sm:text-4xl">
                    Start a poll
                </h1>
                <p className="mt-3 text-ink-soft">
                    Ask a question and give people at least two ways to answer.
                </p>
            </div>

            <form onSubmit={submit} className="rounded-2xl border border-line bg-white p-6 sm:p-8">
                <div>
                    <label htmlFor="question" className="block text-sm font-medium text-ink">
                        Your question
                    </label>
                    <input
                        id="question"
                        type="text"
                        value={data.question}
                        onChange={(e) => setData('question', e.target.value)}
                        placeholder="What should we name the team mascot?"
                        maxLength={255}
                        autoFocus
                        className="mt-2 w-full rounded-lg border border-line bg-marble/40 px-4 py-3 font-serif text-lg text-ink placeholder:text-stone/70 focus:border-verdigris focus:bg-white focus:outline-none focus:ring-2 focus:ring-verdigris/30"
                    />
                    {errors.question && (
                        <p className="mt-2 text-sm text-ochre">{errors.question}</p>
                    )}
                </div>

                <fieldset className="mt-8">
                    <legend className="text-sm font-medium text-ink">Options</legend>
                    {errors.options && <p className="mt-1 text-sm text-ochre">{errors.options}</p>}

                    <ul className="mt-3 space-y-3">
                        {data.options.map((option, index) => (
                            <li key={index}>
                                <div className="flex items-center gap-3">
                                    <span
                                        aria-hidden="true"
                                        className="h-2 w-2 shrink-0 rounded-full bg-stone/50"
                                    />
                                    <input
                                        type="text"
                                        value={option}
                                        onChange={(e) => setOption(index, e.target.value)}
                                        placeholder={`Option ${index + 1}`}
                                        maxLength={120}
                                        aria-label={`Option ${index + 1}`}
                                        className="w-full rounded-lg border border-line bg-marble/40 px-4 py-2.5 text-ink placeholder:text-stone/70 focus:border-verdigris focus:bg-white focus:outline-none focus:ring-2 focus:ring-verdigris/30"
                                    />
                                    <button
                                        type="button"
                                        onClick={() => removeOption(index)}
                                        disabled={data.options.length <= MIN_OPTIONS}
                                        aria-label={`Remove option ${index + 1}`}
                                        className="shrink-0 rounded-md p-2 text-stone transition-colors hover:text-ochre disabled:cursor-not-allowed disabled:opacity-30 focus:outline-none focus-visible:ring-2 focus-visible:ring-verdigris"
                                    >
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                            <path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" />
                                        </svg>
                                    </button>
                                </div>
                                {errors[`options.${index}`] && (
                                    <p className="ml-5 mt-1 text-sm text-ochre">
                                        {errors[`options.${index}`]}
                                    </p>
                                )}
                            </li>
                        ))}
                    </ul>

                    {data.options.length < MAX_OPTIONS && (
                        <button
                            type="button"
                            onClick={addOption}
                            className="mt-3 inline-flex items-center gap-2 rounded-lg border border-dashed border-line px-4 py-2 text-sm font-medium text-ink-soft transition-colors hover:border-verdigris hover:text-verdigris focus:outline-none focus-visible:ring-2 focus-visible:ring-verdigris"
                        >
                            <span aria-hidden="true">+</span> Add option
                        </button>
                    )}
                </fieldset>

                <div className="mt-8 flex items-center gap-4 border-t border-line pt-6">
                    <button
                        type="submit"
                        disabled={processing}
                        className="rounded-full bg-verdigris px-6 py-2.5 text-sm font-medium text-white transition-colors hover:bg-verdigris-deep disabled:opacity-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-verdigris focus-visible:ring-offset-2 focus-visible:ring-offset-white"
                    >
                        {processing ? 'Publishing…' : 'Publish poll'}
                    </button>
                    <Link href="/" className="text-sm text-stone transition-colors hover:text-ink">
                        Cancel
                    </Link>
                </div>
            </form>
        </Layout>
    );
}
