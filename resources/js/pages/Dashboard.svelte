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
  import Plus from 'lucide-svelte/icons/plus';
  import Trash2 from 'lucide-svelte/icons/trash-2';
  import TrendingDown from 'lucide-svelte/icons/trending-down';
  import TrendingUp from 'lucide-svelte/icons/trending-up';
  import Wallet from 'lucide-svelte/icons/wallet';
  import AddTransactionDrawer from '@/components/AddTransactionDrawer.svelte';
  import AppHead from '@/components/AppHead.svelte';
  import Heading from '@/components/Heading.svelte';
  import { Button } from '@/components/ui/button';
  import { Skeleton } from '@/components/ui/skeleton';
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

  let maxCategoryTotal = $derived(
    expenseByCategory.reduce((max, c) => Math.max(max, c.total), 0),
  );

  function handleDelete(txId: number) {
    if (!confirm('هل أنت متأكد من حذف هذه المعاملة؟')) {
return;
}

    router.delete(destroy.url(txId), {
      preserveScroll: true,
      onSuccess: () => router.reload({ only: ['recentTransactions', 'summary', 'expenseByCategory'] }),
    });
  }

  function handleNavigateToTransactions() {
    router.visit(transactionsIndex.url());
  }
</script>

<AppHead title="الرئيسية" />

<div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
  <div class="flex items-center justify-between">
    <Heading
      title="الرئيسية"
      description="ملخص مصروفاتك وإيراداتك لهذا الشهر"
    />
    <Button onclick={() => (drawerOpen = true)}>
      <Plus class="size-4" />
      إضافة معاملة
    </Button>
  </div>

  {#if summary}
    <div class="grid gap-4 md:grid-cols-3">
      <div class="flex items-center gap-4 rounded-xl border p-4">
        <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-red-100 dark:bg-red-900/30">
          <ArrowDownRight class="size-5 text-red-600 dark:text-red-400" />
        </div>
        <div class="min-w-0 flex-1">
          <div class="flex items-center gap-1.5">
            <p class="text-sm text-muted-foreground">المصروفات</p>
            {#if summary.expenseChange !== 0}
              <span class="inline-flex items-center gap-0.5 text-xs {summary.expenseChange > 0 ? 'text-red-600' : 'text-green-600'}">
                {#if summary.expenseChange > 0}
                  <TrendingUp class="size-3" />
                {:else}
                  <TrendingDown class="size-3" />
                {/if}
                {Math.abs(summary.expenseChange)}%
              </span>
            {/if}
          </div>
          <p class="text-xl font-semibold tabular-nums">{summary.expenses.toLocaleString('ar-SA')} ر.س</p>
        </div>
      </div>

      <div class="flex items-center gap-4 rounded-xl border p-4">
        <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30">
          <ArrowUpRight class="size-5 text-green-600 dark:text-green-400" />
        </div>
        <div>
          <p class="text-sm text-muted-foreground">الإيرادات</p>
          <p class="text-xl font-semibold tabular-nums">{summary.income.toLocaleString('ar-SA')} ر.س</p>
        </div>
      </div>

      <div class="flex items-center gap-4 rounded-xl border p-4">
        <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
          <Wallet class="size-5 text-blue-600 dark:text-blue-400" />
        </div>
        <div>
          <p class="text-sm text-muted-foreground">الرصيد</p>
          <p class="text-xl font-semibold tabular-nums">{summary.balance.toLocaleString('ar-SA')} ر.س</p>
        </div>
      </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
      <div class="lg:col-span-2 rounded-xl border p-4">
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-semibold">نظرة على المصروفات</h3>
          <span class="text-xs text-muted-foreground">هذا الشهر</span>
        </div>
        {#if expenseByCategory.length > 0}
          <div class="space-y-3">
            {#each expenseByCategory as item (item.category_id)}
              <div class="flex items-center gap-3">
                <div class="w-24 shrink-0 text-sm text-muted-foreground truncate" title={item.category_name ?? ''}>
                  {item.category_name ?? 'بدون فئة'}
                </div>
                <div class="flex-1 h-5 rounded-sm bg-muted overflow-hidden">
                  <div
                    class="h-full rounded-sm transition-all"
                    style="width: {maxCategoryTotal > 0 ? (item.total / maxCategoryTotal) * 100 : 0}%; background-color: {item.color ?? '#6b7280'}"
                  ></div>
                </div>
                <span class="w-20 shrink-0 text-sm tabular-nums text-end">{item.total.toLocaleString('ar-SA')} ر.س</span>
              </div>
            {/each}
          </div>
        {:else}
          <div class="flex h-64 items-center justify-center rounded-lg border border-dashed">
            <p class="text-sm text-muted-foreground">لا توجد مصروفات هذا الشهر</p>
          </div>
        {/if}
      </div>

      <div class="rounded-xl border p-4">
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-semibold">آخر المعاملات</h3>
        </div>
        <div class="space-y-3">
          {#each recentTransactions as tx (tx.id)}
            <div class="flex items-center justify-between gap-2 border-b pb-3 last:border-0 last:pb-0">
              <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-medium">{tx.description}</p>
                <p class="text-xs text-muted-foreground">
                  {tx.category_name ?? ''}  {tx.date}
                </p>
              </div>
              <div class="flex items-center gap-1.5">
                <span
                  class="tabular-nums text-sm font-semibold {tx.type === 'expense'
                    ? 'text-red-600 dark:text-red-400'
                    : 'text-green-600 dark:text-green-400'}"
                >
                  {tx.type === 'expense' ? '-' : '+'}{tx.amount.toLocaleString('ar-SA')} ر.س
                </span>
                <button
                  type="button"
                  class="shrink-0 rounded p-1 text-muted-foreground hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20"
                  aria-label="حذف"
                  onclick={() => handleDelete(tx.id)}
                >
                  <Trash2 class="size-3.5" />
                </button>
              </div>
            </div>
          {:else}
            <div class="flex h-32 items-center justify-center">
              <p class="text-sm text-muted-foreground">لا توجد معاملات حتى الآن</p>
            </div>
          {/each}
        </div>
        {#if (page.props.recentTransactions ?? []).length > 0}
          <div class="mt-4 pt-3 border-t">
            <button
              type="button"
              class="w-full text-sm text-muted-foreground hover:text-foreground transition-colors"
              onclick={handleNavigateToTransactions}
            >
              عرض جميع المعاملات
            </button>
          </div>
        {/if}
      </div>
    </div>
  {:else}
    <div class="grid gap-4 md:grid-cols-3">
      {#each Array(3) as _}
        <div class="rounded-xl border p-4">
          <div class="flex items-center gap-4">
            <Skeleton class="size-10 rounded-lg" />
            <div class="flex-1 space-y-2">
              <Skeleton class="h-3 w-16" />
              <Skeleton class="h-6 w-24" />
            </div>
          </div>
        </div>
      {/each}
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
      <div class="lg:col-span-2 rounded-xl border p-4">
        <div class="flex items-center justify-between mb-4">
          <Skeleton class="h-5 w-32" />
        </div>
        <div class="space-y-3">
          {#each Array(4) as _}
            <div class="flex items-center gap-3">
              <Skeleton class="h-4 w-20" />
              <Skeleton class="h-5 flex-1 rounded-sm" />
              <Skeleton class="h-4 w-16" />
            </div>
          {/each}
        </div>
      </div>
      <div class="rounded-xl border p-4">
        <div class="mb-4">
          <Skeleton class="h-5 w-28" />
        </div>
        <div class="space-y-3">
          {#each Array(3) as _}
            <div class="flex items-center justify-between gap-2 border-b pb-3">
              <div class="flex-1 space-y-1.5">
                <Skeleton class="h-4 w-24" />
                <Skeleton class="h-3 w-16" />
              </div>
              <Skeleton class="h-4 w-16" />
            </div>
          {/each}
        </div>
      </div>
    </div>
  {/if}
</div>

<AddTransactionDrawer bind:open={drawerOpen} {categories} recentCategories={page.props.recentCategories ?? []} onSuccess={() => router.reload({ only: ['recentTransactions', 'summary', 'expenseByCategory'] })} />
