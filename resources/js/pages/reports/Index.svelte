<script module lang="ts">
  import { reports } from '@/routes';

  export const layout = {
    breadcrumbs: [
      {
        title: 'التقارير',
        href: reports(),
      },
    ],
  };
</script>

<script lang="ts">
  import { usePage, router } from '@inertiajs/svelte';
  import AppHead from '@/components/AppHead.svelte';
  import Heading from '@/components/Heading.svelte';
  import { Skeleton } from '@/components/ui/skeleton';

  interface CategoryTotal {
    name: string;
    icon: string | null;
    color: string;
    total: number;
  }

  interface MonthlyEntry {
    month: string;
    expenses: number;
    income: number;
  }

  interface AvailableMonth {
    year: number;
    month: number;
    label: string;
  }

  interface Summary {
    expenses: number;
    income: number;
  }

  const page = usePage();

  const expenseByCategory: CategoryTotal[] = $derived(
    (page.props.expenseByCategory as CategoryTotal[]) ?? [],
  );
  const incomeByCategory: CategoryTotal[] = $derived(
    (page.props.incomeByCategory as CategoryTotal[]) ?? [],
  );
  const monthlyComparison: MonthlyEntry[] = $derived(
    (page.props.monthlyComparison as MonthlyEntry[]) ?? [],
  );
  const spendingTrends: MonthlyEntry[] = $derived(
    (page.props.spendingTrends as MonthlyEntry[]) ?? [],
  );
  const availableMonths: AvailableMonth[] = $derived(
    (page.props.availableMonths as AvailableMonth[]) ?? [],
  );
  const selectedMonth: number = $derived((page.props.selectedMonth as number) ?? 1);
  const selectedYear: number = $derived((page.props.selectedYear as number) ?? new Date().getFullYear());
  const summary: Summary = $derived(
    (page.props.summary as Summary) ?? { expenses: 0, income: 0 },
  );

  const isLoading = $derived(!page.props || !page.props.summary);

  const totalExpenses = $derived(summary.expenses);
  const totalIncome = $derived(summary.income);

  const trendData = $derived(
    spendingTrends.map((m) => ({ month: m.month, amount: m.expenses })),
  );

  const trendUp = $derived(
    trendData.length >= 2 && trendData[trendData.length - 1].amount > trendData[0].amount,
  );
  const trendDown = $derived(
    trendData.length >= 2 && trendData[trendData.length - 1].amount < trendData[0].amount,
  );

  function pieSegments(total: number, data: { total: number; color: string; name: string }[]) {
    if (total === 0 || data.length === 0) {
return [];
}

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

      const pct = (fraction * 100).toFixed(1);
      const result = { path, color: d.color, name: d.name, total: d.total, pct };
      startAngle = endAngle;

      return result;
    });
  }

  const expenseSegments = $derived(pieSegments(totalExpenses, expenseByCategory));
  const incomeSegments = $derived(pieSegments(totalIncome, incomeByCategory));
  const hasExpenseCategories = $derived(expenseByCategory.length > 0 && totalExpenses > 0);
  const hasIncomeCategories = $derived(incomeByCategory.length > 0 && totalIncome > 0);

  const barMax = $derived(
    monthlyComparison.length > 0
      ? Math.max(...monthlyComparison.map((d) => Math.max(d.income, d.expenses))) || 1
      : 1,
  );

  const barChartHeight = 200;
  const barChartWidth = 400;
  const barPadding = 40;
  const barGroupWidth = $derived(
    monthlyComparison.length > 0
      ? (barChartWidth - barPadding * 2) / monthlyComparison.length
      : 1,
  );
  const barWidth = $derived(barGroupWidth * 0.35);

  const lineMax = $derived(
    trendData.length > 0
      ? Math.max(...trendData.map((d) => d.amount)) * 1.2 || 1
      : 1,
  );

  const lineChartHeight = 200;
  const lineChartWidth = 400;
  const linePadding = 40;

  function linePoints(data: { amount: number }[]) {
    if (data.length === 0) {
return '';
}

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

  const currentMonthLabel = $derived(
    availableMonths.find(
      (m) => m.month === selectedMonth && m.year === selectedYear,
    )?.label ?? `${selectedMonth}/${selectedYear}`,
  );

  function onMonthChange(event: Event) {
    const value = (event.target as HTMLSelectElement).value;
    const [year, month] = value.split('-').map(Number);

    router.get(
      reports.url({ query: { month, year } }),
      {},
      { preserveState: true },
    );
  }
</script>

<AppHead title="التقارير" />

<div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
  <div class="flex items-center justify-between">
    <Heading
      title="التقارير"
      description="رسوم بيانية وتحليلات لمصروفاتك وإيراداتك"
    />
    {#if availableMonths.length > 0}
      <select
        onchange={onMonthChange}
        value={`${selectedYear}-${selectedMonth}`}
        class="rounded-lg border px-3 py-2 text-sm"
      >
        {#each availableMonths as m (m.year + '-' + m.month)}
          <option value={`${m.year}-${m.month}`}>{m.label}</option>
        {/each}
      </select>
    {/if}
  </div>

  {#if isLoading}
    <div class="grid gap-4 md:grid-cols-2">
      {#each Array(2) as _}
        <div class="rounded-xl border p-4">
          <div class="flex items-center gap-4">
            <Skeleton class="size-10 rounded-lg" />
            <div class="space-y-2">
              <Skeleton class="h-3 w-16" />
              <Skeleton class="h-6 w-24" />
            </div>
          </div>
        </div>
      {/each}
    </div>

    <div class="rounded-xl border p-4">
      <Skeleton class="h-5 w-40 mb-4" />
      <div class="flex flex-col items-center gap-6 md:flex-row">
        <Skeleton class="h-64 w-64 rounded-full" />
        <div class="w-full space-y-3">
          {#each Array(4) as _}
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <Skeleton class="size-3 rounded-full" />
                <Skeleton class="h-4 w-20" />
              </div>
              <Skeleton class="h-4 w-16" />
            </div>
          {/each}
        </div>
      </div>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
      <div class="rounded-xl border p-4">
        <Skeleton class="h-5 w-40 mb-4" />
        <Skeleton class="h-48 w-full" />
      </div>
      <div class="rounded-xl border p-4">
        <Skeleton class="h-5 w-32 mb-4" />
        <Skeleton class="h-48 w-full" />
      </div>
    </div>
  {:else}
    <div class="grid gap-4 md:grid-cols-2">
      <div class="flex items-center gap-4 rounded-xl border p-4">
        <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-red-100 dark:bg-red-900/30">
          <svg class="size-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m0 0l-6-6m6 6l6-6" />
          </svg>
        </div>
        <div>
          <p class="text-sm text-muted-foreground">المصروفات</p>
          <p class="text-xl font-semibold tabular-nums">{summary.expenses.toLocaleString('ar-SA')} ر.س</p>
        </div>
      </div>
      <div class="flex items-center gap-4 rounded-xl border p-4">
        <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30">
          <svg class="size-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 20V4m0 0l6 6m-6-6l-6 6" />
          </svg>
        </div>
        <div>
          <p class="text-sm text-muted-foreground">الإيرادات</p>
          <p class="text-xl font-semibold tabular-nums">{summary.income.toLocaleString('ar-SA')} ر.س</p>
        </div>
      </div>
    </div>

    <div class="rounded-xl border p-4">
      <h3 class="mb-1 font-semibold">المصروفات حسب الفئة</h3>
      <p class="mb-4 text-xs text-muted-foreground">{currentMonthLabel}</p>

      {#if hasExpenseCategories}
        <div class="flex flex-col items-center gap-6 md:flex-row">
          <div class="w-full md:w-1/2">
            <svg viewBox="-120 -120 240 240" class="mx-auto h-64 w-64">
              {#each expenseSegments as seg (seg.name + seg.color)}
                <path d={seg.path} fill={seg.color} stroke="var(--color-border, #e5e7eb)" stroke-width="1" class="transition-all duration-200 hover:opacity-80 hover:scale-[1.02] origin-center cursor-pointer">
                  <title>{seg.name}: {seg.total.toLocaleString('ar-SA')} ر.س ({seg.pct}%)</title>
                </path>
              {/each}
            </svg>
          </div>
          <div class="w-full space-y-3 md:w-1/2">
            {#each expenseByCategory as cat (cat.name)}
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
      {:else}
        <div class="flex h-48 items-center justify-center">
          <p class="text-sm text-muted-foreground">لا توجد مصروفات في هذه الفترة</p>
        </div>
      {/if}
    </div>

    {#if hasIncomeCategories}
      <div class="rounded-xl border p-4">
        <h3 class="mb-1 font-semibold">الإيرادات حسب الفئة</h3>
        <p class="mb-4 text-xs text-muted-foreground">{currentMonthLabel}</p>

        <div class="flex flex-col items-center gap-6 md:flex-row">
          <div class="w-full md:w-1/2">
            <svg viewBox="-120 -120 240 240" class="mx-auto h-64 w-64">
              {#each incomeSegments as seg (seg.name + seg.color)}
                <path d={seg.path} fill={seg.color} stroke="var(--color-border, #e5e7eb)" stroke-width="1" class="transition-all duration-200 hover:opacity-80 hover:scale-[1.02] origin-center cursor-pointer">
                  <title>{seg.name}: {seg.total.toLocaleString('ar-SA')} ر.س ({seg.pct}%)</title>
                </path>
              {/each}
            </svg>
          </div>
          <div class="w-full space-y-3 md:w-1/2">
            {#each incomeByCategory as cat (cat.name)}
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
                {totalIncome.toLocaleString('ar-SA')} ر.س
              </span>
            </div>
          </div>
        </div>
      </div>
    {/if}

    <div class="grid gap-6 md:grid-cols-2">
      <div class="rounded-xl border p-4">
        <h3 class="mb-4 font-semibold">الإيرادات والمصروفات الشهرية</h3>

        {#if monthlyComparison.length > 0}
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
            {#each barTicks as tick, i (i)}
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
                fill="currentColor"
                font-size="10"
                class="text-muted-foreground"
              >
                {tick.toLocaleString('ar-SA')}
              </text>
            {/each}

            {#each monthlyComparison as d, i (i)}
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
                class="transition-opacity duration-200 hover:opacity-80 cursor-pointer"
              >
                <title>{d.month}: {d.income.toLocaleString('ar-SA')} ر.س إيرادات</title>
              </rect>
              <rect
                x={groupX + barWidth * 0.55}
                y={y - expenseH}
                width={barWidth}
                height={expenseH}
                fill="#ef4444"
                rx="2"
                class="transition-opacity duration-200 hover:opacity-80 cursor-pointer"
              >
                <title>{d.month}: {d.expenses.toLocaleString('ar-SA')} ر.س مصروفات</title>
              </rect>
              <text
                x={groupX + barGroupWidth / 2}
                y={barChartHeight - 4}
                text-anchor="middle"
                fill="currentColor"
                font-size="10"
                class="text-muted-foreground"
              >
                {d.month}
              </text>
            {/each}
          </svg>
        {:else}
          <div class="flex h-48 items-center justify-center">
            <p class="text-sm text-muted-foreground">لا توجد بيانات كافية</p>
          </div>
        {/if}
      </div>

      <div class="rounded-xl border p-4">
        <h3 class="mb-4 font-semibold">اتجاه المصروفات</h3>

        {#if trendData.length > 0}
          <svg viewBox="0 0 {lineChartWidth} {lineChartHeight + 20}" class="w-full h-auto">
            {#each lineTicks as tick, i (i)}
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
                fill="currentColor"
                font-size="10"
                class="text-muted-foreground"
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

            {#each trendData as d, i (i)}
              {@const stepX = (lineChartWidth - linePadding * 2) / Math.max(trendData.length - 1, 1)}
              {@const cx = linePadding + i * stepX}
              {@const cy = lineChartHeight - linePadding - (d.amount / lineMax) * (lineChartHeight - linePadding * 2)}

              <circle cx={cx} cy={cy} r="3.5" fill="#ef4444" stroke="var(--color-background, white)" stroke-width="2" class="transition-all duration-200 hover:r-[5] cursor-pointer">
                <title>{d.month}: {d.amount.toLocaleString('ar-SA')} ر.س</title>
              </circle>
              <text
                x={cx}
                y={lineChartHeight - 4}
                text-anchor="middle"
                fill="currentColor"
                font-size="10"
                class="text-muted-foreground"
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
        {:else}
          <div class="flex h-48 items-center justify-center">
            <p class="text-sm text-muted-foreground">لا توجد بيانات كافية</p>
          </div>
        {/if}
      </div>
    </div>
  {/if}
</div>
