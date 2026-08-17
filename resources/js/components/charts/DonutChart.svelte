<script lang="ts">
  import CategoryIcon from '@/components/CategoryIcon.svelte';
  import { formatMoney } from '@/lib/format';

  interface Slice {
    name: string;
    icon: string | null;
    color: string;
    total: number;
  }

  let {
    data,
    total,
    centerLabel = 'الإجمالي',
  }: {
    data: Slice[];
    total: number;
    centerLabel?: string;
  } = $props();

  const R = 68;
  const STROKE = 26;
  const C = 2 * Math.PI * R;

  const pctFormatter = new Intl.NumberFormat('ar-SA', {
    maximumFractionDigits: 1,
  });

  // gap between slices, expressed along the circumference (only when >1 slice)
  const gap = $derived(data.length > 1 ? 7 : 0);

  const segments = $derived.by(() => {
    if (total <= 0) {
      return [];
    }

    let acc = 0;

    return data.map((d, i) => {
      const frac = d.total / total;
      const len = frac * C;
      const visible = Math.max(len - gap, 0.001);
      const offset = -acc;
      acc += len;

      return {
        ...d,
        index: i,
        frac,
        len: visible,
        offset,
        pct: pctFormatter.format(frac * 100),
      };
    });
  });

  let mounted = $state(false);
  let hovered = $state<number | null>(null);

  $effect(() => {
    const id = requestAnimationFrame(() => (mounted = true));

    return () => cancelAnimationFrame(id);
  });
</script>

<div class="flex flex-col items-center gap-6 md:flex-row md:items-center md:justify-center md:gap-10">
  <!-- Ring -->
  <div class="relative shrink-0" style="width: 15rem; max-width: 62vw; aspect-ratio: 1;">
    <svg viewBox="-100 -100 200 200" class="h-full w-full -rotate-90 overflow-visible">
      <!-- track -->
      <circle
        cx="0"
        cy="0"
        r={R}
        fill="none"
        stroke="var(--border)"
        stroke-width={STROKE}
        opacity="0.5"
      />
      {#each segments as seg (seg.name + seg.color)}
        <circle
          cx="0"
          cy="0"
          r={R}
          fill="none"
          stroke={seg.color}
          stroke-width={hovered === seg.index ? STROKE + 4 : STROKE}
          stroke-linecap="round"
          stroke-dasharray={mounted ? `${seg.len} ${C - seg.len}` : `0 ${C}`}
          stroke-dashoffset={seg.offset}
          class="cursor-pointer"
          style="
            transition:
              stroke-dasharray 0.85s cubic-bezier(0.22, 1, 0.36, 1) {seg.index * 70}ms,
              stroke-width 0.2s ease,
              opacity 0.2s ease;
            opacity: {hovered === null || hovered === seg.index ? 1 : 0.28};
          "
          role="presentation"
          onpointerenter={() => (hovered = seg.index)}
          onpointerleave={() => (hovered = null)}
        >
          <title>{seg.name}: {formatMoney(seg.total)} ({seg.pct}٪)</title>
        </circle>
      {/each}
    </svg>

    <!-- center label -->
    <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center">
      <span class="text-[0.7rem] font-medium text-muted-foreground">{centerLabel}</span>
      <span class="mt-0.5 text-xl font-bold tabular-nums text-foreground">
        {formatMoney(total, false)}
      </span>
      <span class="text-[0.65rem] text-muted-foreground">ر.س</span>
    </div>
  </div>

  <!-- Legend -->
  <ul class="mx-auto w-full max-w-xs min-w-0 space-y-0.5 md:mx-0 md:w-[19rem]">
    {#each segments as seg (seg.name + seg.color)}
      <li
        class="flex items-center gap-2.5 rounded-xl px-2 py-1.5 transition-colors"
        class:bg-accent={hovered === seg.index}
        onpointerenter={() => (hovered = seg.index)}
        onpointerleave={() => (hovered = null)}
      >
        <CategoryIcon icon={seg.icon} color={seg.color} size="sm" />
        <span class="min-w-0 flex-1 truncate text-sm font-medium text-foreground">
          {seg.name}
        </span>
        <span class="shrink-0 text-sm font-semibold tabular-nums text-foreground">
          {formatMoney(seg.total, false)}
        </span>
        <span
          class="w-9 shrink-0 text-end text-xs tabular-nums text-muted-foreground"
        >
          {seg.pct}٪
        </span>
      </li>
    {/each}
  </ul>
</div>
