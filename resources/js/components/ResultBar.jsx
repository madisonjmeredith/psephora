export default function ResultBar({ label, count, percent, isWinner = false, isYours = false }) {
    return (
        <li>
            <div className="mb-[9px] flex items-baseline justify-between gap-4">
                <span className="flex items-center gap-2 font-display text-[19px] font-semibold text-teal-900">
                    {label}
                    {isYours && (
                        <span className="rounded-full border border-teal-500/50 px-2 py-0.5 font-numeric text-[11px] font-semibold uppercase tracking-normal text-teal-700">
                            your pebble
                        </span>
                    )}
                </span>
                <span className="shrink-0 font-numeric text-[15px] tabular-nums">
                    <span className={`font-bold ${isWinner ? 'text-teal-700' : 'text-teal-900'}`}>
                        {percent}%
                    </span>
                    <span className="font-medium text-ink-400"> · {count}</span>
                </span>
            </div>
            <div className="h-3 overflow-hidden rounded-full border-[1.5px] border-teal-900 bg-yellow-100">
                <div
                    className={`h-full rounded-full transition-[width] duration-700 ease-out motion-reduce:transition-none ${
                        isWinner ? 'bg-yellow-500' : 'bg-yellow-200'
                    }`}
                    style={{ width: `${Math.max(percent, count > 0 ? 3 : 0)}%` }}
                />
            </div>
        </li>
    );
}
