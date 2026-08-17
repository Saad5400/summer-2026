<script lang="ts">
    import CategoryIcon from '@/components/CategoryIcon.svelte';
    import TrendingUp from 'lucide-svelte/icons/trending-up';
    import TrendingDown from 'lucide-svelte/icons/trending-down';
    import Sparkles from 'lucide-svelte/icons/sparkles';
    import Check from 'lucide-svelte/icons/check';

    // Abstract, non-screenshot dashboard mock built from tokens.
    const categories = [
        {
            icon: 'utensils',
            label: 'مطاعم وكافيهات',
            amount: '١٬٨٤٠',
            pct: 82,
            color: 'var(--chart-1)',
        },
        {
            icon: 'shopping-bag',
            label: 'تسوّق',
            amount: '١٬٢٦٠',
            pct: 58,
            color: 'var(--chart-4)',
        },
        {
            icon: 'car',
            label: 'مواصلات',
            amount: '٧٤٠',
            pct: 34,
            color: 'var(--chart-3)',
        },
        {
            icon: 'house',
            label: 'فواتير وسكن',
            amount: '٤٩٠',
            pct: 22,
            color: 'var(--chart-5)',
        },
    ];

    // Donut segments (must sum to 100). Drawn on r=42 circle.
    const segs = [
        { v: 42, color: 'var(--chart-1)' },
        { v: 27, color: 'var(--chart-4)' },
        { v: 18, color: 'var(--chart-3)' },
        { v: 13, color: 'var(--chart-5)' },
    ];
    const C = 2 * Math.PI * 42;
    let acc = 0;
    const arcs = segs.map((s) => {
        const dash = (s.v / 100) * C;
        const seg = { ...s, dash, gap: C - dash, offset: -acc };
        acc += dash;
        return seg;
    });
</script>

<div class="relative select-none">
    <!-- Main dashboard card -->
    <div
        class="relative rounded-2xl border border-border bg-card p-5 shadow-elevated sm:p-6"
    >
        <!-- header -->
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs text-muted-foreground">الرصيد المتاح</p>
                <p
                    class="mt-1 text-3xl font-bold tracking-tight tabular-nums text-foreground sm:text-4xl"
                >
                    ١٢٬٤٥٠<span
                        class="ms-1 text-lg font-semibold text-muted-foreground"
                        >ر.س</span
                    >
                </p>
            </div>
            <span
                class="inline-flex items-center gap-1 rounded-full bg-income-muted px-2.5 py-1 text-xs font-medium text-income"
            >
                <TrendingUp class="size-3.5" />
                <span class="tabular-nums">٨٪</span>
            </span>
        </div>

        <!-- income / expense pills -->
        <div class="mt-4 grid grid-cols-2 gap-3">
            <div class="rounded-xl border border-border bg-background/60 p-3">
                <div class="flex items-center gap-1.5 text-income">
                    <TrendingUp class="size-4" />
                    <span class="text-xs font-medium text-muted-foreground"
                        >الدخل</span
                    >
                </div>
                <p
                    class="mt-1 text-base font-semibold tabular-nums text-foreground"
                >
                    ١٨٬٢٠٠ <span class="text-xs text-muted-foreground">ر.س</span
                    >
                </p>
            </div>
            <div class="rounded-xl border border-border bg-background/60 p-3">
                <div class="flex items-center gap-1.5 text-expense">
                    <TrendingDown class="size-4" />
                    <span class="text-xs font-medium text-muted-foreground"
                        >المصروفات</span
                    >
                </div>
                <p
                    class="mt-1 text-base font-semibold tabular-nums text-foreground"
                >
                    ٥٬٧٥٠ <span class="text-xs text-muted-foreground">ر.س</span>
                </p>
            </div>
        </div>

        <!-- donut + categories -->
        <div class="mt-5 flex items-center gap-5">
            <div class="relative shrink-0">
                <svg viewBox="0 0 100 100" class="size-24 -rotate-90">
                    <circle
                        cx="50"
                        cy="50"
                        r="42"
                        fill="none"
                        stroke="var(--muted)"
                        stroke-width="12"
                    />
                    {#each arcs as a}
                        <circle
                            cx="50"
                            cy="50"
                            r="42"
                            fill="none"
                            stroke={a.color}
                            stroke-width="12"
                            stroke-linecap="round"
                            stroke-dasharray="{a.dash} {a.gap}"
                            stroke-dashoffset={a.offset}
                        />
                    {/each}
                </svg>
                <div
                    class="absolute inset-0 flex flex-col items-center justify-center"
                >
                    <span class="text-[10px] text-muted-foreground"
                        >هذا الشهر</span
                    >
                    <span class="text-sm font-bold tabular-nums text-foreground"
                        >١٢ فئة</span
                    >
                </div>
            </div>

            <div class="flex-1 space-y-2.5">
                {#each categories.slice(0, 3) as c}
                    <div class="flex items-center gap-2.5">
                        <CategoryIcon icon={c.icon} color={c.color} size="sm" />
                        <div class="min-w-0 flex-1">
                            <div
                                class="flex items-baseline justify-between gap-2"
                            >
                                <span
                                    class="truncate text-xs font-medium text-foreground"
                                    >{c.label}</span
                                >
                                <span
                                    class="shrink-0 text-xs font-semibold tabular-nums text-muted-foreground"
                                    >{c.amount}</span
                                >
                            </div>
                            <div
                                class="mt-1 h-1.5 overflow-hidden rounded-full bg-muted"
                            >
                                <div
                                    class="h-full rounded-full"
                                    style="width: {c.pct}%; background: {c.color};"
                                ></div>
                            </div>
                        </div>
                    </div>
                {/each}
            </div>
        </div>
    </div>

    <!-- Floating AI auto-categorize toast -->
    <div
        class="absolute -bottom-6 -start-4 hidden items-center gap-2.5 rounded-xl border border-border bg-card/95 px-3.5 py-2.5 shadow-elevated backdrop-blur animate-fade-in-up sm:flex"
        style="animation-delay: 520ms;"
    >
        <span
            class="flex size-8 items-center justify-center rounded-lg bg-primary text-primary-foreground"
        >
            <Sparkles class="size-4" />
        </span>
        <div class="text-start">
            <p class="text-[11px] leading-tight text-muted-foreground">
                صُنّفت تلقائياً
            </p>
            <p
                class="flex items-center gap-1 text-xs font-semibold text-foreground"
            >
                قهوة الصباح · مطاعم
                <Check class="size-3.5 text-income" />
            </p>
        </div>
    </div>

    <!-- Floating balance chip -->
    <div
        class="absolute -top-5 -end-3 hidden rounded-xl border border-border bg-card/95 px-3 py-2 shadow-elevated backdrop-blur animate-fade-in-up md:block"
        style="animation-delay: 640ms;"
    >
        <p class="text-[10px] text-muted-foreground">وفّرت هذا الشهر</p>
        <p class="text-sm font-bold tabular-nums text-income">+٢٬٤٥٠ ر.س</p>
    </div>
</div>
