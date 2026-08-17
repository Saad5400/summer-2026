<script lang="ts">
    import { formatMoney, formatNumber } from '@/lib/format';

    interface Entry {
        month: string;
        short?: string;
        income: number;
        expenses: number;
    }

    let { data }: { data: Entry[] } = $props();

    // --- geometry (viewBox units) ---
    const W = 360;
    const H = 236;
    const pad = { top: 16, right: 46, bottom: 30, left: 12 };

    const plotLeft = pad.left;
    const plotRight = W - pad.right;
    const plotTop = pad.top;
    const plotBottom = H - pad.bottom;
    const plotW = plotRight - plotLeft;
    const plotH = plotBottom - plotTop;

    function niceMax(v: number): number {
        if (v <= 0) {
            return 1;
        }
        const p = Math.pow(10, Math.floor(Math.log10(v)));
        const n = v / p;
        let m: number;
        if (n <= 1) {
            m = 1;
        } else if (n <= 2) {
            m = 2;
        } else if (n <= 2.5) {
            m = 2.5;
        } else if (n <= 5) {
            m = 5;
        } else {
            m = 10;
        }

        return m * p;
    }

    const rawMax = $derived(
        Math.max(1, ...data.map((d) => Math.max(d.income, d.expenses))),
    );
    const max = $derived(niceMax(rawMax));

    const ticks = $derived([0, 1, 2, 3, 4].map((i) => (max / 4) * i));

    const groupSpacing = $derived(
        data.length > 0 ? plotW / data.length : plotW,
    );
    const barW = $derived(Math.min(groupSpacing * 0.26, 18));
    const barGap = 4;

    // RTL: index 0 (oldest) sits at the far right, newest flows left.
    function groupCenter(i: number): number {
        return plotRight - (i + 0.5) * groupSpacing;
    }

    function y(val: number): number {
        return plotBottom - (val / max) * plotH;
    }

    let mounted = $state(false);
    $effect(() => {
        const id = requestAnimationFrame(() => (mounted = true));

        return () => cancelAnimationFrame(id);
    });
</script>

<div class="flex flex-col gap-3">
    <!-- legend -->
    <div class="flex items-center gap-4">
        <span class="flex items-center gap-1.5 text-xs text-muted-foreground">
            <span
                class="inline-block size-2.5 rounded-full"
                style="background: var(--income)"
            ></span>
            إيرادات
        </span>
        <span class="flex items-center gap-1.5 text-xs text-muted-foreground">
            <span
                class="inline-block size-2.5 rounded-full"
                style="background: var(--expense)"
            ></span>
            مصروفات
        </span>
    </div>

    <svg
        viewBox="0 0 {W} {H}"
        class="h-auto w-full overflow-visible"
        role="img"
        aria-label="مقارنة الإيرادات والمصروفات الشهرية"
    >
        <!-- gridlines + y labels (labels on the right for RTL) -->
        {#each ticks as tick (tick)}
            {@const gy = y(tick)}
            <line
                x1={plotLeft}
                y1={gy}
                x2={plotRight}
                y2={gy}
                stroke="var(--border)"
                stroke-width="1"
                opacity={tick === 0 ? 0.9 : 0.45}
            />
            <text
                x={plotRight + 6}
                y={gy + 3}
                text-anchor="start"
                font-size="9"
                fill="var(--muted-foreground)"
                class="tabular-nums"
            >
                {formatNumber(tick)}
            </text>
        {/each}

        {#each data as d, i (d.month + i)}
            {@const cx = groupCenter(i)}
            {@const incH = (d.income / max) * plotH}
            {@const expH = (d.expenses / max) * plotH}
            {@const incX = cx + barGap / 2}
            {@const expX = cx - barGap / 2 - barW}

            <!-- income bar (right side of the pair, RTL-first) -->
            <rect
                x={incX}
                y={y(d.income)}
                width={barW}
                height={Math.max(incH, 0)}
                rx="3"
                fill="var(--income)"
                class="cursor-pointer transition-opacity duration-200 hover:opacity-80"
                style="transform: scaleY({mounted
                    ? 1
                    : 0}); transform-box: fill-box; transform-origin: center bottom; transition: transform 0.7s cubic-bezier(0.22, 1, 0.36, 1) {i *
                    60}ms, opacity 0.2s ease;"
            >
                <title>{d.month} — إيرادات: {formatMoney(d.income)}</title>
            </rect>

            <!-- expense bar -->
            <rect
                x={expX}
                y={y(d.expenses)}
                width={barW}
                height={Math.max(expH, 0)}
                rx="3"
                fill="var(--expense)"
                class="cursor-pointer transition-opacity duration-200 hover:opacity-80"
                style="transform: scaleY({mounted
                    ? 1
                    : 0}); transform-box: fill-box; transform-origin: center bottom; transition: transform 0.7s cubic-bezier(0.22, 1, 0.36, 1) {i *
                    60 +
                    40}ms, opacity 0.2s ease;"
            >
                <title>{d.month} — مصروفات: {formatMoney(d.expenses)}</title>
            </rect>

            <!-- month label (short Arabic label to avoid overlap) -->
            <text
                x={cx}
                y={H - 10}
                text-anchor="middle"
                font-size="10"
                fill="var(--muted-foreground)"
            >
                {d.short ?? d.month}
            </text>
        {/each}
    </svg>
</div>
