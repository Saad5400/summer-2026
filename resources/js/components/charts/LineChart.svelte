<script lang="ts">
  import { formatMoney, formatNumber } from '@/lib/format';

  interface Point {
    month: string;
    short?: string;
    amount: number;
  }

  let {
    data,
    color = 'var(--expense)',
  }: {
    data: Point[];
    color?: string;
  } = $props();

  const uid = $props.id();
  const gradId = `mizan-area-${uid}`;

  const W = 360;
  const H = 236;
  const pad = { top: 18, right: 46, bottom: 30, left: 12 };

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

  const max = $derived(niceMax(Math.max(1, ...data.map((d) => d.amount)) * 1.1));
  const ticks = $derived([0, 1, 2, 3, 4].map((i) => (max / 4) * i));

  // RTL: index 0 (oldest) at far right, newest flows left → x decreases with i.
  const points = $derived.by(() =>
    data.map((d, i) => {
      const step = data.length > 1 ? plotW / (data.length - 1) : 0;
      const x = plotRight - i * step;
      const yv = plotBottom - (d.amount / max) * plotH;

      return { ...d, x, y: yv };
    }),
  );

  // Smooth cubic path (Catmull-Rom → Bézier) through the ordered points.
  function smoothLine(pts: { x: number; y: number }[]): string {
    if (pts.length === 0) {
      return '';
    }
    if (pts.length === 1) {
      return `M ${pts[0].x} ${pts[0].y}`;
    }

    let d = `M ${pts[0].x} ${pts[0].y}`;
    for (let i = 0; i < pts.length - 1; i++) {
      const p0 = pts[i - 1] ?? pts[i];
      const p1 = pts[i];
      const p2 = pts[i + 1];
      const p3 = pts[i + 2] ?? p2;
      const t = 0.18;
      const c1x = p1.x + (p2.x - p0.x) * t;
      const c1y = p1.y + (p2.y - p0.y) * t;
      const c2x = p2.x - (p3.x - p1.x) * t;
      const c2y = p2.y - (p3.y - p1.y) * t;
      d += ` C ${c1x} ${c1y}, ${c2x} ${c2y}, ${p2.x} ${p2.y}`;
    }

    return d;
  }

  const linePath = $derived(smoothLine(points));
  const areaPath = $derived(
    points.length > 0
      ? `${linePath} L ${points[points.length - 1].x} ${plotBottom} L ${points[0].x} ${plotBottom} Z`
      : '',
  );

  let mounted = $state(false);
  $effect(() => {
    const id = requestAnimationFrame(() => (mounted = true));

    return () => cancelAnimationFrame(id);
  });
</script>

<svg viewBox="0 0 {W} {H}" class="h-auto w-full overflow-visible" role="img" aria-label="اتجاه المصروفات عبر الأشهر">
  <defs>
    <linearGradient id={gradId} x1="0" y1="0" x2="0" y2="1">
      <stop offset="0%" stop-color={color} stop-opacity="0.28" />
      <stop offset="100%" stop-color={color} stop-opacity="0" />
    </linearGradient>
  </defs>

  <!-- gridlines + y labels -->
  {#each ticks as tick (tick)}
    {@const gy = plotBottom - (tick / max) * plotH}
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

  <!-- area fill -->
  <path
    d={areaPath}
    fill="url(#{gradId})"
    style="opacity: {mounted ? 1 : 0}; transition: opacity 0.9s ease 0.3s;"
  />

  <!-- line draw-in via normalized pathLength -->
  <path
    d={linePath}
    fill="none"
    stroke={color}
    stroke-width="2.5"
    stroke-linecap="round"
    stroke-linejoin="round"
    pathLength="1"
    stroke-dasharray="1"
    stroke-dashoffset={mounted ? 0 : 1}
    style="transition: stroke-dashoffset 1.1s cubic-bezier(0.4, 0, 0.2, 1);"
  />

  <!-- points -->
  {#each points as p, i (p.month + i)}
    <circle
      cx={p.x}
      cy={p.y}
      r="3.5"
      fill="var(--background)"
      stroke={color}
      stroke-width="2.5"
      class="cursor-pointer transition-[r] duration-200 hover:r-[5]"
      style="opacity: {mounted ? 1 : 0}; transition: opacity 0.4s ease {0.5 + i * 0.06}s;"
    >
      <title>{p.month}: {formatMoney(p.amount)}</title>
    </circle>
    <!-- thin out labels when crowded: every 2nd point, always the last -->
    {#if data.length <= 6 || i % 2 === 0 || i === data.length - 1}
      <text
        x={p.x}
        y={H - 10}
        text-anchor="middle"
        font-size="10"
        fill="var(--muted-foreground)"
      >
        {p.short ?? p.month}
      </text>
    {/if}
  {/each}
</svg>
