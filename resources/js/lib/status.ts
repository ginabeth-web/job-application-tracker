import type { ApplicationStatus } from '@/types';

export interface StatusStyle {
    dot: string;
    title: string;
    column: string;
    count: string;
    stripe: string;
    empty: string;
    badge: string;
}

// Class strings are written out in full so Tailwind can detect them.
const styles: Record<ApplicationStatus, StatusStyle> = {
    wishlist: {
        dot: 'bg-slate-500 dark:bg-slate-400',
        title: 'text-slate-700 dark:text-slate-300',
        column: 'bg-slate-50 dark:bg-slate-950/60',
        count: 'bg-slate-500 text-white dark:bg-slate-600',
        stripe: 'border-t-[3px] border-t-slate-500',
        empty: 'border-slate-300 text-slate-500 dark:border-slate-700 dark:text-slate-400',
        badge: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
    },
    applied: {
        dot: 'bg-blue-500 dark:bg-blue-400',
        title: 'text-blue-700 dark:text-blue-300',
        column: 'bg-blue-50 dark:bg-blue-950/60',
        count: 'bg-blue-500 text-white dark:bg-blue-600',
        stripe: 'border-t-[3px] border-t-blue-500',
        empty: 'border-blue-300 text-blue-500 dark:border-blue-800 dark:text-blue-400',
        badge: 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300',
    },
    interviewing: {
        dot: 'bg-amber-500 dark:bg-amber-400',
        title: 'text-amber-700 dark:text-amber-300',
        column: 'bg-amber-50 dark:bg-amber-950/60',
        count: 'bg-amber-500 text-white dark:bg-amber-600',
        stripe: 'border-t-[3px] border-t-amber-500',
        empty: 'border-amber-300 text-amber-600 dark:border-amber-800 dark:text-amber-400',
        badge: 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
    },
    offer: {
        dot: 'bg-emerald-500 dark:bg-emerald-400',
        title: 'text-emerald-700 dark:text-emerald-300',
        column: 'bg-emerald-50 dark:bg-emerald-950/60',
        count: 'bg-emerald-500 text-white dark:bg-emerald-600',
        stripe: 'border-t-[3px] border-t-emerald-500',
        empty: 'border-emerald-300 text-emerald-600 dark:border-emerald-800 dark:text-emerald-400',
        badge: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300',
    },
    rejected: {
        dot: 'bg-rose-500 dark:bg-rose-400',
        title: 'text-rose-700 dark:text-rose-300',
        column: 'bg-rose-50 dark:bg-rose-950/60',
        count: 'bg-rose-500 text-white dark:bg-rose-600',
        stripe: 'border-t-[3px] border-t-rose-500',
        empty: 'border-rose-300 text-rose-500 dark:border-rose-800 dark:text-rose-400',
        badge: 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300',
    },
};

export function statusStyles(status: ApplicationStatus): StatusStyle {
    return styles[status];
}
