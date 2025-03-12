export default function ResultBar({ label, count, percent, isWinner = false, isYours = false }) {
    return (
        <li className="space-y-2">
            <div className="flex items-baseline justify-between gap-4">
                <span className="flex items-center gap-2 font-serif text-lg text-ink">
                    {label}
                    {isYours && (
                        <span className="rounded-full border border-verdigris/40 px-2 py-0.5 font-sans text-[11px] font-medium uppercase tracking-wide text-verdigris-deep">
                            your pebble
                        </span>
                    )}
                </span>
                <span className="shrink-0 font-mono text-sm tabular-nums">
                    <span className={`font-semibold ${isWinner ? 'text-verdigris-deep' : 'text-ink'}`}>
                        {percent}%
                    </span>
                    <span className="text-stone"> · {count}</span>
                </span>
            </div>
            <div className="h-2.5 w-full overflow-hidden rounded-full bg-marble-deep">
                <div
                    className={`h-full rounded-full transition-[width] duration-700 ease-out motion-reduce:transition-none ${
                        isWinner ? 'bg-verdigris' : 'bg-stone/70'
                    }`}
                    style={{ width: `${Math.max(percent, count > 0 ? 2 : 0)}%` }}
                />
            </div>
        </li>
    );
}
