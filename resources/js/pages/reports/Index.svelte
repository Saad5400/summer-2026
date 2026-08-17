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
    import ArrowDownLeft from 'lucide-svelte/icons/arrow-down-left';
    import ArrowUpRight from 'lucide-svelte/icons/arrow-up-right';
    import ChartColumn from 'lucide-svelte/icons/chart-column';
    import ChartPie from 'lucide-svelte/icons/chart-pie';
    import TrendingDown from 'lucide-svelte/icons/trending-down';
    import TrendingUp from 'lucide-svelte/icons/trending-up';
    import AppHead from '@/components/AppHead.svelte';
    import Heading from '@/components/Heading.svelte';
    import BarChart from '@/components/charts/BarChart.svelte';
    import DonutChart from '@/components/charts/DonutChart.svelte';
    import LineChart from '@/components/charts/LineChart.svelte';
    import { Badge } from '@/components/ui/badge';
    import * as Card from '@/components/ui/card';
    import * as Empty from '@/components/ui/empty';
    import * as Select from '@/components/ui/select';
    import { Skeleton } from '@/components/ui/skeleton';
    import * as Tabs from '@/components/ui/tabs';
    import { formatMoney } from '@/lib/format';

    interface CategoryTotal {
        name: string;
        icon: string | null;
        color: string;
        total: number;
    }

    interface MonthlyEntry {
        month: string;
        short?: string;
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
    const selectedMonth: number = $derived(
        (page.props.selectedMonth as number) ?? 1,
    );
    const selectedYear: number = $derived(
        (page.props.selectedYear as number) ?? new Date().getFullYear(),
    );
    const summary: Summary = $derived(
        (page.props.summary as Summary) ?? { expenses: 0, income: 0 },
    );

    const isLoading = $derived(!page.props || !page.props.summary);

    const totalExpenses = $derived(summary.expenses);
    const totalIncome = $derived(summary.income);

    const hasExpenseCategories = $derived(
        expenseByCategory.length > 0 && totalExpenses > 0,
    );
    const hasIncomeCategories = $derived(
        incomeByCategory.length > 0 && totalIncome > 0,
    );

    const trendData = $derived(
        spendingTrends.map((m) => ({
            month: m.month,
            short: m.short ?? m.month,
            amount: m.expenses,
        })),
    );

    const trendUp = $derived(
        trendData.length >= 2 &&
            trendData[trendData.length - 1].amount > trendData[0].amount,
    );
    const trendDown = $derived(
        trendData.length >= 2 &&
            trendData[trendData.length - 1].amount < trendData[0].amount,
    );

    const currentMonthLabel = $derived(
        availableMonths.find(
            (m) => m.month === selectedMonth && m.year === selectedYear,
        )?.label ?? `${selectedMonth}/${selectedYear}`,
    );

    const selectedValue = $derived(`${selectedYear}-${selectedMonth}`);

    function onMonthChange(value: string | undefined) {
        if (!value) {
            return;
        }
        const [year, month] = value.split('-').map(Number);

        router.get(
            reports.url({ query: { month, year } }),
            {},
            { preserveState: true },
        );
    }
</script>

<AppHead title="التقارير" />

<div class="flex flex-1 flex-col gap-6 px-4 py-6 md:px-6 md:py-8">
    <!-- Header + month picker -->
    <div class="flex flex-wrap items-center justify-between gap-3">
        <Heading
            title="التقارير"
            description="رسوم بيانية وتحليلات لمصروفاتك وإيراداتك"
        />
        {#if availableMonths.length > 0}
            <Select.Root
                type="single"
                value={selectedValue}
                onValueChange={onMonthChange}
            >
                <Select.Trigger
                    class="min-w-[9.5rem] justify-between shadow-soft"
                    size="default"
                >
                    <span class="font-medium">{currentMonthLabel}</span>
                </Select.Trigger>
                <Select.Content>
                    {#each availableMonths as m (m.year + '-' + m.month)}
                        <Select.Item
                            value={`${m.year}-${m.month}`}
                            label={m.label}
                        />
                    {/each}
                </Select.Content>
            </Select.Root>
        {/if}
    </div>

    {#if isLoading}
        <!-- Skeleton -->
        <div class="grid gap-4 sm:grid-cols-2">
            {#each Array(2) as _, i (i)}
                <div
                    class="flex items-center gap-4 rounded-2xl border border-border bg-card p-4"
                >
                    <Skeleton class="size-11 rounded-xl" />
                    <div class="space-y-2">
                        <Skeleton class="h-3 w-16" />
                        <Skeleton class="h-6 w-28" />
                    </div>
                </div>
            {/each}
        </div>
        <div class="rounded-2xl border border-border bg-card p-5">
            <Skeleton class="mb-5 h-5 w-40" />
            <div class="flex flex-col items-center gap-6 md:flex-row">
                <Skeleton class="size-60 rounded-full" />
                <div class="w-full flex-1 space-y-3">
                    {#each Array(4) as _, i (i)}
                        <div class="flex items-center gap-3">
                            <Skeleton class="size-8 rounded-lg" />
                            <Skeleton class="h-4 flex-1" />
                            <Skeleton class="h-4 w-16" />
                        </div>
                    {/each}
                </div>
            </div>
        </div>
        <div class="grid gap-6 md:grid-cols-2">
            {#each Array(2) as _, i (i)}
                <div class="rounded-2xl border border-border bg-card p-5">
                    <Skeleton class="mb-5 h-5 w-40" />
                    <Skeleton class="h-52 w-full rounded-xl" />
                </div>
            {/each}
        </div>
    {:else}
        <!-- Summary stat cards -->
        <div class="grid gap-4 sm:grid-cols-2">
            <Card.Root class="animate-fade-in-up shadow-soft">
                <Card.Content class="flex items-center gap-4">
                    <span
                        class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-income/12 text-income"
                    >
                        <ArrowUpRight class="size-5" />
                    </span>
                    <div class="min-w-0">
                        <p class="text-sm text-muted-foreground">الإيرادات</p>
                        <p
                            class="truncate text-2xl font-bold tabular-nums text-income"
                        >
                            {formatMoney(totalIncome)}
                        </p>
                    </div>
                </Card.Content>
            </Card.Root>

            <Card.Root
                class="animate-fade-in-up shadow-soft"
                style="animation-delay: 60ms"
            >
                <Card.Content class="flex items-center gap-4">
                    <span
                        class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-expense/12 text-expense"
                    >
                        <ArrowDownLeft class="size-5" />
                    </span>
                    <div class="min-w-0">
                        <p class="text-sm text-muted-foreground">المصروفات</p>
                        <p
                            class="truncate text-2xl font-bold tabular-nums text-expense"
                        >
                            {formatMoney(totalExpenses)}
                        </p>
                    </div>
                </Card.Content>
            </Card.Root>
        </div>

        <!-- Category donut -->
        <Card.Root
            class="animate-fade-in-up shadow-soft"
            style="animation-delay: 120ms"
        >
            <Card.Header>
                <Card.Title class="flex items-center gap-2">
                    <ChartPie class="size-4 text-muted-foreground" />
                    التوزيع حسب الفئة
                </Card.Title>
                <Card.Description>{currentMonthLabel}</Card.Description>
            </Card.Header>
            <Card.Content>
                {#if hasExpenseCategories && hasIncomeCategories}
                    <Tabs.Root value="expense">
                        <Tabs.List class="mb-6 w-full">
                            <Tabs.Trigger value="expense" class="flex-1"
                                >المصروفات</Tabs.Trigger
                            >
                            <Tabs.Trigger value="income" class="flex-1"
                                >الإيرادات</Tabs.Trigger
                            >
                        </Tabs.List>
                        <Tabs.Content value="expense">
                            <DonutChart
                                data={expenseByCategory}
                                total={totalExpenses}
                            />
                        </Tabs.Content>
                        <Tabs.Content value="income">
                            <DonutChart
                                data={incomeByCategory}
                                total={totalIncome}
                            />
                        </Tabs.Content>
                    </Tabs.Root>
                {:else if hasExpenseCategories}
                    <DonutChart
                        data={expenseByCategory}
                        total={totalExpenses}
                    />
                {:else if hasIncomeCategories}
                    <DonutChart data={incomeByCategory} total={totalIncome} />
                {:else}
                    <Empty.Root class="border-0">
                        <Empty.Media variant="icon"><ChartPie /></Empty.Media>
                        <Empty.Title>لا توجد بيانات</Empty.Title>
                        <Empty.Description
                            >لا توجد مصروفات أو إيرادات في هذه الفترة</Empty.Description
                        >
                    </Empty.Root>
                {/if}
            </Card.Content>
        </Card.Root>

        <div class="grid gap-6 md:grid-cols-2">
            <!-- Monthly comparison bars -->
            <Card.Root
                class="animate-fade-in-up shadow-soft"
                style="animation-delay: 180ms"
            >
                <Card.Header>
                    <Card.Title class="flex items-center gap-2">
                        <ChartColumn class="size-4 text-muted-foreground" />
                        الإيرادات والمصروفات الشهرية
                    </Card.Title>
                    <Card.Description>آخر الأشهر</Card.Description>
                </Card.Header>
                <Card.Content>
                    {#if monthlyComparison.length > 0}
                        <BarChart data={monthlyComparison} />
                    {:else}
                        <Empty.Root class="border-0">
                            <Empty.Media variant="icon"
                                ><ChartColumn /></Empty.Media
                            >
                            <Empty.Title>لا توجد بيانات كافية</Empty.Title>
                            <Empty.Description
                                >سجّل معاملات على مدى عدة أشهر للمقارنة</Empty.Description
                            >
                        </Empty.Root>
                    {/if}
                </Card.Content>
            </Card.Root>

            <!-- Spending trend line -->
            <Card.Root
                class="animate-fade-in-up shadow-soft"
                style="animation-delay: 240ms"
            >
                <Card.Header>
                    <Card.Title class="flex items-center gap-2">
                        <TrendingUp class="size-4 text-muted-foreground" />
                        اتجاه المصروفات
                    </Card.Title>
                    <Card.Action>
                        {#if trendData.length >= 2}
                            {#if trendUp}
                                <Badge
                                    class="gap-1 border-transparent bg-expense/12 text-expense"
                                >
                                    <TrendingUp class="size-3.5" /> مرتفع
                                </Badge>
                            {:else if trendDown}
                                <Badge
                                    class="gap-1 border-transparent bg-income/12 text-income"
                                >
                                    <TrendingDown class="size-3.5" /> منخفض
                                </Badge>
                            {:else}
                                <Badge variant="secondary">مستقر</Badge>
                            {/if}
                        {/if}
                    </Card.Action>
                    <Card.Description
                        >تطور المصروفات عبر الأشهر</Card.Description
                    >
                </Card.Header>
                <Card.Content>
                    {#if trendData.length > 0}
                        <LineChart data={trendData} />
                    {:else}
                        <Empty.Root class="border-0">
                            <Empty.Media variant="icon"
                                ><TrendingUp /></Empty.Media
                            >
                            <Empty.Title>لا توجد بيانات كافية</Empty.Title>
                            <Empty.Description
                                >لم تُسجَّل مصروفات كافية لعرض الاتجاه</Empty.Description
                            >
                        </Empty.Root>
                    {/if}
                </Card.Content>
            </Card.Root>
        </div>
    {/if}
</div>
