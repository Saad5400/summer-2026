<script module lang="ts">
  import { dashboard } from '@/routes';

  export const layout = {
    breadcrumbs: [
      {
        title: 'الرئيسية',
        href: dashboard(),
      },
    ],
  };
</script>

<script lang="ts">
  import { usePage, router } from '@inertiajs/svelte';
  import ArrowDownRight from 'lucide-svelte/icons/arrow-down-right';
  import ArrowUpRight from 'lucide-svelte/icons/arrow-up-right';
  import ChevronLeft from 'lucide-svelte/icons/chevron-left';
  import Inbox from 'lucide-svelte/icons/inbox';
  import PieChart from 'lucide-svelte/icons/pie-chart';
  import Plus from 'lucide-svelte/icons/plus';
  import Trash2 from 'lucide-svelte/icons/trash-2';
  import type { Snippet } from 'svelte';
  import AddTransactionDrawer from '@/components/AddTransactionDrawer.svelte';
  import AppHead from '@/components/AppHead.svelte';
  import CategoryIcon from '@/components/CategoryIcon.svelte';
  import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
  } from '@/components/ui/alert-dialog';
  import { Button } from '@/components/ui/button';
  import { Card, CardContent } from '@/components/ui/card';
  import {
    Empty,
    EmptyDescription,
    EmptyHeader,
    EmptyMedia,
    EmptyTitle,
  } from '@/components/ui/empty';
  import { Skeleton } from '@/components/ui/skeleton';
  import {
    formatMoney,
    formatNumber,
    formatRelativeDate,
    formatSignedMoney,
  } from '@/lib/format';
  import { index as transactionsIndex, destroy } from '@/routes/transactions';
  import type { Category } from '@/types';

  interface PageProps {
    summary: {
      expenses: number;
      income: number;
      balance: number;
      expenseChange: number;
    };
    expenseByCategory: Array<{
      category_id: number;
      category_name: string | null;
      icon: string | null;
      color: string | null;
      total: number;
    }>;
    incomeByCategory: Array<{
      category_id: number;
      category_name: string | null;
      icon: string | null;
      color: string | null;
      total: number;
    }>;
    recentTransactions: Array<{
      id: number;
      amount: number;
      description: string;
      date: string;
      type: 'expense' | 'income';
      category_name: string | null;
      icon: string | null;
      color: string | null;
    }>;
    categories: Category[];
    recentCategories: Category[];
  }

  let page = usePage<PageProps>();
  let drawerOpen = $state(false);

  let summary = $derived(page.props.summary);
  let expenseByCategory = $derived(page.props.expenseByCategory ?? []);
  let recentTransactions = $derived((page.props.recentTransactions ?? []).slice(0, 5));
  let categories = $derived(page.props.categories ?? []);
  let hasMoreTransactions = $derived((page.props.recentTransactions ?? []).length > 0);

  let totalExpenses = $derived(
    expenseByCategory.reduce((sum, c) => sum + c.total, 0),
  );

  // Current month label (matches the app's Arabic date convention).
  const monthLabel = new Intl.DateTimeFormat('ar-SA', {
    month: 'long',
  }).format(new Date());

  // Grow the proportion bars once mounted for a subtle entrance.
  let mounted = $state(false);
  $effect(() => {
    mounted = true;
  });

  // Delete flow via AlertDialog.
  let deleteOpen = $state(false);
  let deleteTarget = $state<{ id: number; description: string } | null>(null);

  function requestDelete(tx: { id: number; description: string }) {
    deleteTarget = { id: tx.id, description: tx.description };
    deleteOpen = true;
  }

  function confirmDelete() {
    const target = deleteTarget;
    if (!target) {
      return;
    }

    router.delete(destroy.url(target.id), {
      preserveScroll: true,
      onSuccess: () =>
        router.reload({ only: ['recentTransactions', 'summary', 'expenseByCategory'] }),
    });

    deleteOpen = false;
    deleteTarget = null;
  }

  function handleNavigateToTransactions() {
    router.visit(transactionsIndex.url());
  }
</script>

<AppHead title="الرئيسية" />

{#snippet statCard(
  label: string,
  value: number,
  icon: string,
  tint: string,
  emphasized: boolean,
  delay: number,
  trend?: Snippet,
)}
  <Card
    class="animate-fade-in-up shadow-soft {emphasized
      ? 'bg-primary/[0.04] ring-primary/15'
      : ''}"
    style="animation-delay: {delay}ms"
  >
    <CardContent class="flex items-center gap-4">
      <CategoryIcon {icon} color={tint} size="lg" />
      <div class="min-w-0 flex-1">
        <div class="flex items-center gap-2">
          <p class="truncate text-sm text-muted-foreground">{label}</p>
          {#if trend}{@render trend()}{/if}
        </div>
        <p
          class="mt-0.5 truncate text-2xl font-bold tracking-tight tabular-nums {emphasized
            ? 'text-primary'
            : 'text-foreground'}"
          title={formatMoney(value)}
        >
          {formatMoney(value)}
        </p>
      </div>
    </CardContent>
  </Card>
{/snippet}

{#snippet expenseTrend()}
  {#if summary.expenseChange !== 0}
    {@const up = summary.expenseChange > 0}
    <span
      class="inline-flex items-center gap-0.5 rounded-full px-1.5 py-0.5 text-[0.7rem] font-semibold tabular-nums {up
        ? 'bg-expense-muted text-expense'
        : 'bg-income-muted text-income'}"
    >
      {#if up}
        <ArrowUpRight class="size-3 cn-rtl-flip" />
      {:else}
        <ArrowDownRight class="size-3 cn-rtl-flip" />
      {/if}
      {formatNumber(Math.abs(summary.expenseChange))}٪
    </span>
  {/if}
{/snippet}

<div class="flex flex-1 flex-col gap-6 px-4 py-6 md:px-6 md:py-8">
  <!-- Hero header -->
  <header
    class="animate-fade-in-up flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
  >
    <div class="min-w-0">
      <p class="text-sm text-muted-foreground">أهلاً بك 👋</p>
      <h1 class="mt-1 text-2xl font-bold tracking-tight text-foreground">
        نظرة على أموالك
      </h1>
      <p class="mt-1 text-sm text-muted-foreground">
        ملخص مصروفاتك وإيراداتك لشهر {monthLabel}
      </p>
    </div>
    <Button
      size="lg"
      class="h-11 shrink-0 gap-2 shadow-soft transition-transform active:scale-95 max-sm:w-full"
      onclick={() => (drawerOpen = true)}
    >
      <Plus class="size-4" />
      إضافة معاملة
    </Button>
  </header>

  {#if summary}
    <!-- Summary stat cards -->
    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      {@render statCard('الرصيد', summary.balance, 'wallet', 'var(--primary)', true, 0)}
      {@render statCard('المصروفات', summary.expenses, 'receipt', 'var(--expense)', false, 60, expenseTrend)}
      {@render statCard('الإيرادات', summary.income, 'trending-up', 'var(--income)', false, 120)}
    </section>

    <!-- Breakdown + recent transactions -->
    <div class="grid gap-6 lg:grid-cols-5">
      <!-- Expense breakdown -->
      <Card
        class="animate-fade-in-up shadow-soft lg:col-span-3"
        style="animation-delay: 160ms"
      >
        <CardContent class="flex h-full flex-col gap-4">
          <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-foreground">نظرة على المصروفات</h2>
            <span
              class="rounded-full bg-muted px-2.5 py-1 text-xs font-medium text-muted-foreground"
            >
              {monthLabel}
            </span>
          </div>

          {#if expenseByCategory.length > 0}
            <ul class="flex flex-col gap-4">
              {#each expenseByCategory as item, i (item.category_id)}
                {@const pct = totalExpenses > 0 ? (item.total / totalExpenses) * 100 : 0}
                <li
                  class="animate-fade-in-up flex items-center gap-3"
                  style="animation-delay: {200 + i * 60}ms"
                >
                  <CategoryIcon icon={item.icon} color={item.color} size="md" />
                  <div class="min-w-0 flex-1">
                    <div class="flex items-baseline justify-between gap-2">
                      <span class="truncate text-sm font-medium text-foreground">
                        {item.category_name ?? 'بدون فئة'}
                      </span>
                      <span class="shrink-0 text-sm font-semibold tabular-nums text-foreground">
                        {formatMoney(item.total)}
                      </span>
                    </div>
                    <div class="mt-1.5 flex items-center gap-2">
                      <div class="h-2 flex-1 overflow-hidden rounded-full bg-muted">
                        <div
                          class="h-full rounded-full transition-[width] duration-700 ease-out"
                          style="width: {mounted ? Math.max(pct, 3) : 0}%; background-color: {item.color ?? 'var(--primary)'}"
                        ></div>
                      </div>
                      <span
                        class="w-9 shrink-0 text-end text-xs font-medium tabular-nums text-muted-foreground"
                      >
                        {formatNumber(Math.round(pct))}٪
                      </span>
                    </div>
                  </div>
                </li>
              {/each}
            </ul>
          {:else}
            <Empty class="flex-1 border border-dashed border-border">
              <EmptyHeader>
                <EmptyMedia variant="icon" class="size-11 rounded-xl">
                  <PieChart class="size-5" />
                </EmptyMedia>
                <EmptyTitle>لا توجد مصروفات بعد</EmptyTitle>
                <EmptyDescription>
                  أضف أول معاملة لتظهر نظرة مفصّلة على مصروفاتك حسب الفئة.
                </EmptyDescription>
              </EmptyHeader>
            </Empty>
          {/if}
        </CardContent>
      </Card>

      <!-- Recent transactions -->
      <Card
        class="animate-fade-in-up shadow-soft lg:col-span-2"
        style="animation-delay: 220ms"
      >
        <CardContent class="flex h-full flex-col gap-4">
          <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-foreground">آخر المعاملات</h2>
          </div>

          {#if recentTransactions.length > 0}
            <ul class="-mx-2 flex flex-col">
              {#each recentTransactions as tx, i (tx.id)}
                <li
                  class="animate-fade-in-up group flex items-center gap-3 rounded-lg px-2 py-2 transition-colors hover:bg-muted/60"
                  style="animation-delay: {240 + i * 50}ms"
                >
                  <CategoryIcon icon={tx.icon} color={tx.color} size="md" />
                  <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium text-foreground">
                      {tx.description}
                    </p>
                    <p class="truncate text-xs text-muted-foreground">
                      {tx.category_name ? `${tx.category_name} · ` : ''}{formatRelativeDate(tx.date)}
                    </p>
                  </div>
                  <div class="flex shrink-0 items-center gap-1">
                    <span
                      class="text-sm font-semibold tabular-nums {tx.type === 'expense'
                        ? 'text-expense'
                        : 'text-income'}"
                    >
                      {formatSignedMoney(tx.amount, tx.type)}
                    </span>
                    <button
                      type="button"
                      class="flex size-8 items-center justify-center rounded-md text-muted-foreground opacity-0 transition-all hover:bg-destructive/10 hover:text-destructive focus-visible:opacity-100 active:scale-95 group-hover:opacity-100 max-sm:opacity-100"
                      aria-label="حذف المعاملة"
                      onclick={() => requestDelete(tx)}
                    >
                      <Trash2 class="size-4" />
                    </button>
                  </div>
                </li>
              {/each}
            </ul>

            {#if hasMoreTransactions}
              <Button
                variant="ghost"
                class="mt-auto h-10 w-full justify-center gap-1 text-muted-foreground hover:text-foreground"
                onclick={handleNavigateToTransactions}
              >
                عرض جميع المعاملات
                <ChevronLeft class="size-4" />
              </Button>
            {/if}
          {:else}
            <Empty class="flex-1 border border-dashed border-border">
              <EmptyHeader>
                <EmptyMedia variant="icon" class="size-11 rounded-xl">
                  <Inbox class="size-5" />
                </EmptyMedia>
                <EmptyTitle>لا توجد معاملات</EmptyTitle>
                <EmptyDescription>
                  ستظهر معاملاتك الأخيرة هنا بمجرد إضافتها.
                </EmptyDescription>
              </EmptyHeader>
              <Button
                size="sm"
                class="gap-1.5 active:scale-95"
                onclick={() => (drawerOpen = true)}
              >
                <Plus class="size-4" />
                إضافة معاملة
              </Button>
            </Empty>
          {/if}
        </CardContent>
      </Card>
    </div>
  {:else}
    <!-- Loading skeletons -->
    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      {#each Array(3) as _}
        <Card class="shadow-soft">
          <CardContent class="flex items-center gap-4">
            <Skeleton class="size-12 rounded-2xl" />
            <div class="flex-1 space-y-2">
              <Skeleton class="h-3 w-16" />
              <Skeleton class="h-7 w-28" />
            </div>
          </CardContent>
        </Card>
      {/each}
    </section>

    <div class="grid gap-6 lg:grid-cols-5">
      <Card class="shadow-soft lg:col-span-3">
        <CardContent class="space-y-4">
          <Skeleton class="h-5 w-36" />
          <div class="space-y-4">
            {#each Array(5) as _}
              <div class="flex items-center gap-3">
                <Skeleton class="size-10 rounded-xl" />
                <div class="flex-1 space-y-2">
                  <Skeleton class="h-3.5 w-24" />
                  <Skeleton class="h-2 w-full rounded-full" />
                </div>
              </div>
            {/each}
          </div>
        </CardContent>
      </Card>
      <Card class="shadow-soft lg:col-span-2">
        <CardContent class="space-y-4">
          <Skeleton class="h-5 w-28" />
          <div class="space-y-3">
            {#each Array(4) as _}
              <div class="flex items-center gap-3">
                <Skeleton class="size-10 rounded-xl" />
                <div class="flex-1 space-y-1.5">
                  <Skeleton class="h-3.5 w-24" />
                  <Skeleton class="h-3 w-16" />
                </div>
                <Skeleton class="h-4 w-14" />
              </div>
            {/each}
          </div>
        </CardContent>
      </Card>
    </div>
  {/if}
</div>

<AlertDialog bind:open={deleteOpen}>
  <AlertDialogContent>
    <AlertDialogHeader>
      <AlertDialogTitle>حذف المعاملة؟</AlertDialogTitle>
      <AlertDialogDescription>
        سيتم حذف {deleteTarget?.description ? `«${deleteTarget.description}»` : 'هذه المعاملة'} نهائياً ولا يمكن التراجع عن هذا الإجراء.
      </AlertDialogDescription>
    </AlertDialogHeader>
    <AlertDialogFooter>
      <AlertDialogCancel>إلغاء</AlertDialogCancel>
      <AlertDialogAction variant="destructive" onclick={confirmDelete}>
        حذف
      </AlertDialogAction>
    </AlertDialogFooter>
  </AlertDialogContent>
</AlertDialog>

<AddTransactionDrawer
  bind:open={drawerOpen}
  {categories}
  recentCategories={page.props.recentCategories ?? []}
  onSuccess={() => router.reload({ only: ['recentTransactions', 'summary', 'expenseByCategory'] })}
/>
