import { Link } from '@inertiajs/react';

// The design-system button: a filled, tactile control. Primary is a sunflower
// slab with dark-teal ink and a 2px darker-yellow "lip"; ghost is quiet text.
const VARIANTS = {
    primary:
        'bg-yellow-500 text-teal-900 border-[1.5px] border-teal-900 shadow-button hover:bg-yellow-400 active:translate-y-[2px] active:shadow-none',
    ghost: 'bg-transparent text-ink-600 hover:bg-cream-200 hover:text-teal-900',
};

const SIZES = {
    sm: 'px-4 py-2 text-sm',
    default: 'px-5 py-2.5 text-[15px]',
    lg: 'px-7 py-3.5 text-base',
};

export default function Button({
    href,
    variant = 'primary',
    size = 'default',
    className = '',
    children,
    ...props
}) {
    const classes = [
        'inline-flex items-center justify-center gap-2 rounded-[14px] font-display font-semibold transition-[background-color,transform,box-shadow] duration-150 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500 focus-visible:ring-offset-2 focus-visible:ring-offset-cream-100 disabled:cursor-not-allowed disabled:opacity-40 disabled:active:translate-y-0 disabled:active:shadow-button motion-reduce:transition-none',
        VARIANTS[variant],
        SIZES[size],
        className,
    ]
        .filter(Boolean)
        .join(' ');

    if (href) {
        return (
            <Link href={href} className={classes} {...props}>
                {children}
            </Link>
        );
    }

    return (
        <button className={classes} {...props}>
            {children}
        </button>
    );
}
