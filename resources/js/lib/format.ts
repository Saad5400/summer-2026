/**
 * Formatting helpers for the Mizan app (Arabic / SAR).
 */

const numberFormatter = new Intl.NumberFormat('ar-SA', {
    maximumFractionDigits: 0,
});

const decimalFormatter = new Intl.NumberFormat('ar-SA', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
});

/** Localised integer amount, e.g. ١٢٬٥٠٠ */
export function formatNumber(value: number): string {
    return numberFormatter.format(Math.round(value ?? 0));
}

/** Localised amount with 2 decimals */
export function formatDecimal(value: number): string {
    return decimalFormatter.format(value ?? 0);
}

/** Amount followed by the SAR unit, e.g. ١٢٬٥٠٠ ر.س */
export function formatMoney(value: number, withUnit = true): string {
    const n = formatNumber(value);

    return withUnit ? `${n} ر.س` : n;
}

/** Signed money for transactions: +/- prefix based on type */
export function formatSignedMoney(
    value: number,
    type: 'income' | 'expense',
): string {
    const sign = type === 'income' ? '+' : '−';

    return `${sign}${formatMoney(Math.abs(value))}`;
}

const dateFormatter = new Intl.DateTimeFormat('ar-SA', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
});

const shortDateFormatter = new Intl.DateTimeFormat('ar-SA', {
    day: 'numeric',
    month: 'short',
});

function toDate(value: string | Date): Date {
    return value instanceof Date ? value : new Date(value);
}

export function formatDate(value: string | Date): string {
    try {
        return dateFormatter.format(toDate(value));
    } catch {
        return String(value);
    }
}

export function formatShortDate(value: string | Date): string {
    try {
        return shortDateFormatter.format(toDate(value));
    } catch {
        return String(value);
    }
}

/** Relative-ish label: اليوم / أمس / else short date */
export function formatRelativeDate(value: string | Date): string {
    try {
        const d = toDate(value);
        const today = new Date();
        const startOfToday = new Date(
            today.getFullYear(),
            today.getMonth(),
            today.getDate(),
        );
        const diffDays = Math.round(
            (startOfToday.getTime() -
                new Date(d.getFullYear(), d.getMonth(), d.getDate()).getTime()) /
                86400000,
        );

        if (diffDays === 0) return 'اليوم';
        if (diffDays === 1) return 'أمس';

        return formatShortDate(d);
    } catch {
        return String(value);
    }
}
