import { Head, Link, useForm } from '@inertiajs/react';
import Layout from '../../components/Layout';
import Button from '../../components/Button';

const MAX_OPTIONS = 10;
const MIN_OPTIONS = 2;

const fieldClass =
    'w-full rounded-[14px] border-[1.5px] border-cream-300 bg-white px-4 text-teal-900 placeholder:text-ink-400 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/30';

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
                <h1 className="mt-5 font-display text-[36px] font-extrabold tracking-[-0.02em] text-teal-900 sm:text-[44px]">
                    Start a poll
                </h1>
                <p className="mt-3 text-[18px] text-ink-600">
                    Ask a question and give people at least two ways to answer.
                </p>
            </div>

            <form
                onSubmit={submit}
                className="rounded-[18px] border-[1.5px] border-teal-900 bg-white p-7 shadow-stack sm:p-10"
            >
                <div className="mb-8">
                    <label
                        htmlFor="question"
                        className="mb-3 block font-display text-[15px] font-semibold text-teal-900"
                    >
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
                        className={`${fieldClass} py-3.5 text-[18px]`}
                    />
                    {errors.question && (
                        <p className="mt-2 text-sm text-ochre">{errors.question}</p>
                    )}
                </div>

                <fieldset>
                    <legend className="mb-3.5 font-display text-[15px] font-semibold text-teal-900">
                        Options
                    </legend>
                    {errors.options && <p className="mb-2 text-sm text-ochre">{errors.options}</p>}

                    <div className="flex flex-col gap-3.5">
                        {data.options.map((option, index) => (
                            <div key={index}>
                                <div className="flex items-center gap-3.5">
                                    <span
                                        aria-hidden="true"
                                        className="h-[9px] w-[9px] shrink-0 rounded-full bg-teal-500"
                                    />
                                    <input
                                        type="text"
                                        value={option}
                                        onChange={(e) => setOption(index, e.target.value)}
                                        placeholder={`Option ${index + 1}`}
                                        maxLength={120}
                                        aria-label={`Option ${index + 1}`}
                                        className={`${fieldClass} min-w-0 flex-1 py-2.5`}
                                    />
                                    <button
                                        type="button"
                                        onClick={() => removeOption(index)}
                                        disabled={data.options.length <= MIN_OPTIONS}
                                        aria-label={`Remove option ${index + 1}`}
                                        className="inline-flex h-[34px] w-[34px] shrink-0 items-center justify-center rounded-[10px] text-ink-400 transition-colors hover:text-teal-900 disabled:cursor-not-allowed disabled:opacity-30 disabled:hover:text-ink-400 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500"
                                    >
                                        <svg
                                            width="17"
                                            height="17"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            strokeWidth="2"
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            aria-hidden="true"
                                        >
                                            <path d="M18 6L6 18" />
                                            <path d="M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                {errors[`options.${index}`] && (
                                    <p className="ml-[23px] mt-1 text-sm text-ochre">
                                        {errors[`options.${index}`]}
                                    </p>
                                )}
                            </div>
                        ))}
                    </div>

                    {data.options.length < MAX_OPTIONS && (
                        <button
                            type="button"
                            onClick={addOption}
                            className="mt-4 inline-flex items-center gap-2 rounded-[14px] border-[1.5px] border-dashed border-teal-900 bg-transparent px-[18px] py-2.5 font-display text-[15px] font-semibold text-teal-900 transition-colors hover:bg-cream-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500"
                        >
                            <svg
                                width="16"
                                height="16"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                strokeWidth="2.2"
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                aria-hidden="true"
                            >
                                <path d="M12 5v14" />
                                <path d="M5 12h14" />
                            </svg>
                            Add option
                        </button>
                    )}
                </fieldset>

                <div className="my-7 h-px bg-cream-300" />

                <div className="flex items-center gap-5">
                    <Button type="submit" disabled={processing}>
                        {processing ? 'Publishing…' : 'Publish poll'}
                    </Button>
                    <Button href="/" variant="ghost">
                        Cancel
                    </Button>
                </div>
            </form>
        </Layout>
    );
}
