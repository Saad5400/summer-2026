<script module lang="ts">
  export const layout = {
    breadcrumbs: [
      {
        title: 'التقارير',
        href: '/reports',
      },
    ],
  };
</script>

<script lang="ts">
  import AppHead from '@/components/AppHead.svelte';
  import Heading from '@/components/Heading.svelte';
  import { EXPENSE_CATEGORIES } from '@/types';

  const expensesByCategory = EXPENSE_CATEGORIES.map((c) => ({
    ...c,
    total: Math.floor(Math.random() * 500) + 50,
  }));

  const totalExpenses = expensesByCategory.reduce((sum, c) => sum + c.total, 0);

  const monthlyData = [
    { month: 'يناير', income: 8500, expenses: 2300 },
    { month: 'فبراير', income: 8500, expenses: 2800 },
    { month: 'مارس', income: 8500, expenses: 1950 },
    { month: 'أبريل', income: 8500, expenses: 3100 },
    { month: 'مايو', income: 8500, expenses: 2600 },
    { month: 'يونيو', income: 8500, expenses: 2540 },
  ];

  const trendData = $derived(
    monthlyData.map((m) => ({ month: m.month, amount: m.expenses })),
  );

  const trendUp = $derived(
    trendData.length >= 2 && trendData[trendData.length - 1].amount > trendData[0].amount,
  );

  const trendDown = $derived(
    trendData.length >= 2 && trendData[trendData.length - 1].amount < trendData[0].amount,
  );

  function pieSegments(total: number, data: { total: number; color: string }[]) {
    let startAngle = 0;

    return data.map((d) => {
      const fraction = d.total / total;
      const angle = fraction * Math.PI * 2;
      const endAngle = startAngle + angle;
      const largeArc = angle > Math.PI ? 1 : 0;
      const outerR = 100;
      const innerR = 55;

      const x1 = outerR * Math.sin(startAngle);
      const y1 = -outerR * Math.cos(startAngle);
      const x2 = outerR * Math.sin(endAngle);
      const y2 = -outerR * Math.cos(endAngle);
      const ix1 = innerR * Math.sin(startAngle);
      const iy1 = -innerR * Math.cos(startAngle);
      const ix2 = innerR * Math.sin(endAngle);
      const iy2 = -innerR * Math.cos(endAngle);

      const path = [
        `M ${ix1} ${iy1}`,
        `L ${x1} ${y1}`,
        `A ${outerR} ${outerR} 0 ${largeArc} 1 ${x2} ${y2}`,
        `L ${ix2} ${iy2}`,
        `A ${innerR} ${innerR} 0 ${largeArc} 0 ${ix1} ${iy1}`,
        'Z',
      ].join(' ');

      const result = { path, color: d.color };
      startAngle = endAngle;

      return result;
    });
  }

  const segments = $derived(pieSegments(totalExpenses, expensesByCategory));

  const barMax = $derived(Math.max(...monthlyData.map((d) => Math.max(d.income, d.expenses))));

  const barChartHeight = 200;
  const barChartWidth = 400;
  const barPadding = 40;
  const barGroupWidth = (barChartWidth - barPadding * 2) / monthlyData.length;
  const barWidth = barGroupWidth * 0.35;

  const lineMax = $derived(Math.max(...trendData.map((d) => d.amount)) * 1.2);

  const lineChartHeight = 200;
  const lineChartWidth = 400;
  const linePadding = 40;

  function linePoints(data: { amount: number }[]) {
    const max = lineMax;
    const stepX = (lineChartWidth - linePadding * 2) / Math.max(data.length - 1, 1);

    return data
      .map(
        (d, i) =>
          `${linePadding + i * stepX},${lineChartHeight - linePadding - (d.amount / max) * (lineChartHeight - linePadding * 2)}`,
      )
      .join(' ');
  }

  const linePath = $derived(linePoints(trendData));

  function yAxisTicks(max: number, count: number) {
    const ticks: number[] = [];

    for (let i = 0; i <= count; i++) {
      ticks.push(Math.round((max / count) * i));
    }

    return ticks;
  }

  const barTicks = $derived(yAxisTicks(barMax, 4));
  const lineTicks = $derived(yAxisTicks(lineMax, 4));
</script>

<AppHead title="التقارير" />

<div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
  <Heading
    title="التقارير"
    description="رسوم بيانية وتحليلات لمصروفاتك وإيراداتك"
  />

  <div class="rounded-xl border p-4">
    <h3 class="mb-4 font-semibold">المصروفات حسب الفئة</h3>
    <div class="flex flex-col items-center gap-6 md:flex-row">
      <div class="w-full md:w-1/2">
        <svg viewBox="-120 -120 240 240" class="mx-auto h-64 w-64">
          {#each segments as seg, i (i)}
            <path d={seg.path} fill={seg.color} stroke="var(--color-border, #e5e7eb)" stroke-width="1" />
          {/each}
        </svg>
      </div>

      <div class="w-full space-y-3 md:w-1/2">
        {#each expensesByCategory as cat (cat.id)}
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span
                class="inline-block size-3 shrink-0 rounded-full"
                style="background-color: {cat.color}"
              ></span>
              <span class="text-sm">{cat.name}</span>
            </div>
            <span class="text-sm tabular-nums text-muted-foreground">
              {cat.total.toLocaleString('ar-SA')} ر.س
            </span>
          </div>
        {/each}
        <div class="flex items-center justify-between border-t pt-3">
          <span class="text-sm font-medium">المجموع</span>
          <span class="text-sm font-semibold tabular-nums">
            {totalExpenses.toLocaleString('ar-SA')} ر.س
          </span>
        </div>
      </div>
    </div>
  </div>

  <div class="grid gap-6 md:grid-cols-2">
    <div class="rounded-xl border p-4">
      <h3 class="mb-4 font-semibold">الإيرادات والمصروفات الشهرية</h3>

      <div class="flex items-center gap-6 mb-3">
        <div class="flex items-center gap-2">
          <span class="inline-block size-3 rounded-sm" style="background-color: #16a34a"></span>
          <span class="text-xs text-muted-foreground">إيرادات</span>
        </div>
        <div class="flex items-center gap-2">
          <span class="inline-block size-3 rounded-sm" style="background-color: #ef4444"></span>
          <span class="text-xs text-muted-foreground">مصروفات</span>
        </div>
      </div>

      <svg viewBox="0 0 {barChartWidth} {barChartHeight + 20}" class="w-full h-auto">
        {#each barTicks as tick (tick)}
          {@const y = barChartHeight - barPadding - (tick / barMax) * (barChartHeight - barPadding * 2)}
          <line
            x1={barPadding}
            y1={y}
            x2={barChartWidth - barPadding}
            y2={y}
            stroke="var(--color-border, #e5e7eb)"
            stroke-width="1"
          />
          <text
            x={barPadding - 4}
            y={y + 4}
            text-anchor="end"
            class="fill-muted-foreground"
            font-size="10"
          >
            {tick.toLocaleString('ar-SA')}
          </text>
        {/each}

        {#each monthlyData as d, i (d.month)}
          {@const groupX = barPadding + i * barGroupWidth}
          {@const incomeH = (d.income / barMax) * (barChartHeight - barPadding * 2)}
          {@const expenseH = (d.expenses / barMax) * (barChartHeight - barPadding * 2)}
          {@const y = barChartHeight - barPadding}

          <rect
            x={groupX + barWidth * 0.1}
            y={y - incomeH}
            width={barWidth}
            height={incomeH}
            fill="#16a34a"
            rx="2"
          />
          <rect
            x={groupX + barWidth * 0.55}
            y={y - expenseH}
            width={barWidth}
            height={expenseH}
            fill="#ef4444"
            rx="2"
          />
          <text
            x={groupX + barGroupWidth / 2}
            y={barChartHeight - 4}
            text-anchor="middle"
            class="fill-muted-foreground"
            font-size="10"
          >
            {d.month}
          </text>
        {/each}
      </svg>
    </div>

    <div class="rounded-xl border p-4">
      <h3 class="mb-4 font-semibold">اتجاه المصروفات</h3>

      <svg viewBox="0 0 {lineChartWidth} {lineChartHeight + 20}" class="w-full h-auto">
        {#each lineTicks as tick (tick)}
          {@const y = lineChartHeight - linePadding - (tick / lineMax) * (lineChartHeight - linePadding * 2)}
          <line
            x1={linePadding}
            y1={y}
            x2={lineChartWidth - linePadding}
            y2={y}
            stroke="var(--color-border, #e5e7eb)"
            stroke-width="1"
          />
          <text
            x={linePadding - 4}
            y={y + 4}
            text-anchor="end"
            class="fill-muted-foreground"
            font-size="10"
          >
            {tick.toLocaleString('ar-SA')}
          </text>
        {/each}

        <polyline
          points={linePath}
          fill="none"
          stroke="#ef4444"
          stroke-width="2"
          stroke-linejoin="round"
          stroke-linecap="round"
        />

        {#each trendData as d, i (d.month)}
          {@const stepX = (lineChartWidth - linePadding * 2) / Math.max(trendData.length - 1, 1)}
          {@const cx = linePadding + i * stepX}
          {@const cy = lineChartHeight - linePadding - (d.amount / lineMax) * (lineChartHeight - linePadding * 2)}

          <circle cx={cx} cy={cy} r="3.5" fill="#ef4444" stroke="var(--color-background, white)" stroke-width="2" />
          <text
            x={cx}
            y={lineChartHeight - 4}
            text-anchor="middle"
            class="fill-muted-foreground"
            font-size="10"
          >
            {d.month}
          </text>
        {/each}
      </svg>

      <div class="mt-3 flex items-center gap-2">
        <span class="text-xs text-muted-foreground">الاتجاه</span>
        {#if trendUp}
          <span class="text-xs font-medium text-red-600 dark:text-red-400">
            مرتفع &#x2197;
          </span>
        {:else if trendDown}
          <span class="text-xs font-medium text-green-600 dark:text-green-400">
            منخفض &#x2198;
          </span>
        {:else}
          <span class="text-xs text-muted-foreground">مستقر</span>
        {/if}
      </div>
    </div>
  </div>
</div>
