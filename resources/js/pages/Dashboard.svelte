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
  import AppHead from '@/components/AppHead.svelte';
  import Heading from '@/components/Heading.svelte';
  import { Button } from '@/components/ui/button';
  import AddTransactionDrawer from '@/components/AddTransactionDrawer.svelte';
  import ArrowDownRight from 'lucide-svelte/icons/arrow-down-right';
  import ArrowUpRight from 'lucide-svelte/icons/arrow-up-right';
  import Wallet from 'lucide-svelte/icons/wallet';
  import Plus from 'lucide-svelte/icons/plus';
  import type { Transaction, TransactionSummary, TransactionFormData } from '@/types';

  interface Props {
    summary?: TransactionSummary;
    recentTransactions?: Transaction[];
  }

  let {
    summary = {
      total_expenses: 2540,
      total_income: 8500,
      balance: 5960,
      transaction_count: 24,
    },
    recentTransactions = [
      { id: 1, amount: 45, description: 'مشتريات بقالة', date: '2026-07-14', type: 'expense' as const, category: { id: 1, name: 'طعام ومشروبات', icon: '', color: '#ef4444', type: 'expense' as const }, created_at: '2026-07-14' },
      { id: 2, amount: 120, description: 'فاتورة كهرباء', date: '2026-07-13', type: 'expense' as const, category: { id: 4, name: 'فواتير', icon: '', color: '#22c55e', type: 'expense' as const }, created_at: '2026-07-13' },
      { id: 3, amount: 8500, description: 'راتب شهري', date: '2026-07-01', type: 'income' as const, category: { id: 10, name: 'راتب', icon: '', color: '#16a34a', type: 'income' as const }, created_at: '2026-07-01' },
      { id: 4, amount: 350, description: 'مطعم عشاء', date: '2026-07-12', type: 'expense' as const, category: { id: 1, name: 'طعام ومشروبات', icon: '', color: '#ef4444', type: 'expense' as const }, created_at: '2026-07-12' },
      { id: 5, amount: 200, description: 'بنزين', date: '2026-07-11', type: 'expense' as const, category: { id: 2, name: 'مواصلات', icon: '', color: '#f97316', type: 'expense' as const }, created_at: '2026-07-11' },
    ],
  }: Props = $props();

  let drawerOpen = $state(false);

  function handleAdd(data: TransactionFormData) {
    console.log('New transaction:', data);
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

  <div class="grid gap-4 md:grid-cols-3">
    <div class="flex items-center gap-4 rounded-xl border p-4">
      <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-red-100 dark:bg-red-900/30">
        <ArrowDownRight class="size-5 text-red-600 dark:text-red-400" />
      </div>
      <div>
        <p class="text-sm text-muted-foreground">المصروفات</p>
        <p class="text-xl font-semibold tabular-nums">{summary.total_expenses.toLocaleString('ar-SA')} ر.س</p>
      </div>
    </div>

    <div class="flex items-center gap-4 rounded-xl border p-4">
      <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30">
        <ArrowUpRight class="size-5 text-green-600 dark:text-green-400" />
      </div>
      <div>
        <p class="text-sm text-muted-foreground">الإيرادات</p>
        <p class="text-xl font-semibold tabular-nums">{summary.total_income.toLocaleString('ar-SA')} ر.س</p>
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
      <div class="flex h-64 items-center justify-center rounded-lg border border-dashed">
        <p class="text-sm text-muted-foreground">الرسوم البيانية - قريباً</p>
      </div>
    </div>

    <div class="rounded-xl border p-4">
      <h3 class="font-semibold mb-4">آخر المعاملات</h3>
      <div class="space-y-3">
        {#each recentTransactions.slice(0, 5) as tx (tx.id)}
          <div class="flex items-center justify-between border-b pb-3 last:border-0 last:pb-0">
            <div class="min-w-0 flex-1">
              <p class="truncate text-sm font-medium">{tx.description}</p>
              <p class="text-xs text-muted-foreground">
                {tx.category?.name ?? ''}  {tx.date}
              </p>
            </div>
            <span
              class="tabular-nums text-sm font-semibold {tx.type === 'expense'
                ? 'text-red-600 dark:text-red-400'
                : 'text-green-600 dark:text-green-400'}"
            >
              {tx.type === 'expense' ? '-' : '+'}{tx.amount.toLocaleString('ar-SA')} ر.س
            </span>
          </div>
        {:else}
          <p class="text-sm text-muted-foreground">لا توجد معاملات حتى الآن</p>
        {/each}
      </div>
    </div>
  </div>
</div>

<AddTransactionDrawer bind:open={drawerOpen} onAdd={handleAdd} />
