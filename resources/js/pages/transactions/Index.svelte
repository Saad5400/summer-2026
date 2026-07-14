<script module lang="ts">
  export const layout = {
    breadcrumbs: [
      {
        title: 'المعاملات',
        href: '/transactions',
      },
    ],
  };
</script>

<script lang="ts">
  import AppHead from '@/components/AppHead.svelte';
  import Heading from '@/components/Heading.svelte';
  import { Button } from '@/components/ui/button';
  import { Input } from '@/components/ui/input';
  import * as Select from '@/components/ui/select';
  import * as Table from '@/components/ui/table';
  import AddTransactionDrawer from '@/components/AddTransactionDrawer.svelte';
  import ArrowDownRight from 'lucide-svelte/icons/arrow-down-right';
  import ArrowUpRight from 'lucide-svelte/icons/arrow-up-right';
  import Plus from 'lucide-svelte/icons/plus';
  import Search from 'lucide-svelte/icons/search';
  import { EXPENSE_CATEGORIES, INCOME_CATEGORIES } from '@/types';
  import type { Transaction, TransactionFormData } from '@/types';

  interface Props {
    transactions?: Transaction[];
  }

  let {
    transactions = [
      { id: 1, amount: 45, description: 'مشتريات بقالة', date: '2026-07-14', type: 'expense' as const, category: { id: 1, name: 'طعام ومشروبات', icon: '', color: '#ef4444', type: 'expense' as const }, created_at: '2026-07-14' },
      { id: 2, amount: 120, description: 'فاتورة كهرباء', date: '2026-07-13', type: 'expense' as const, category: { id: 4, name: 'فواتير', icon: '', color: '#22c55e', type: 'expense' as const }, created_at: '2026-07-13' },
      { id: 3, amount: 8500, description: 'راتب شهري', date: '2026-07-01', type: 'income' as const, category: { id: 10, name: 'راتب', icon: '', color: '#16a34a', type: 'income' as const }, created_at: '2026-07-01' },
      { id: 4, amount: 350, description: 'مطعم عشاء', date: '2026-07-12', type: 'expense' as const, category: { id: 1, name: 'طعام ومشروبات', icon: '', color: '#ef4444', type: 'expense' as const }, created_at: '2026-07-12' },
      { id: 5, amount: 200, description: 'بنزين', date: '2026-07-11', type: 'expense' as const, category: { id: 2, name: 'مواصلات', icon: '', color: '#f97316', type: 'expense' as const }, created_at: '2026-07-11' },
      { id: 6, amount: 80, description: 'أدوية', date: '2026-07-10', type: 'expense' as const, category: { id: 7, name: 'صحة', icon: '', color: '#ec4899', type: 'expense' as const }, created_at: '2026-07-10' },
      { id: 7, amount: 1500, description: 'عمل حر - تصميم', date: '2026-07-08', type: 'income' as const, category: { id: 11, name: 'عمل حر', icon: '', color: '#0ea5e9', type: 'income' as const }, created_at: '2026-07-08' },
      { id: 8, amount: 65, description: 'اشتراك Netflix', date: '2026-07-07', type: 'expense' as const, category: { id: 5, name: 'ترفيه', icon: '', color: '#06b6d4', type: 'expense' as const }, created_at: '2026-07-07' },
    ],
  }: Props = $props();

  let drawerOpen = $state(false);
  let filterType = $state('');
  let filterCategory = $state('');
  let searchQuery = $state('');

  const allCategories = $derived([...EXPENSE_CATEGORIES, ...INCOME_CATEGORIES]);

  const filteredTransactions = $derived(
    transactions.filter((tx) => {
      if (filterType && tx.type !== filterType) return false;
      if (filterCategory && tx.category?.id !== parseInt(filterCategory)) return false;
      if (searchQuery && !tx.description.includes(searchQuery)) return false;
      return true;
    }),
  );

  function handleAdd(data: TransactionFormData) {
    console.log('New transaction:', data);
  }
</script>

<AppHead title="المعاملات" />

<div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
  <div class="flex items-center justify-between">
    <Heading
      title="المعاملات"
      description="جميع مصروفاتك وإيراداتك"
    />
    <Button onclick={() => (drawerOpen = true)}>
      <Plus class="size-4" />
      إضافة معاملة
    </Button>
  </div>

  <div class="flex flex-wrap items-center gap-3 rounded-xl border p-3">
    <div class="relative flex-1 min-w-[200px]">
      <Search class="absolute right-2.5 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
      <Input
        class="pr-8"
        placeholder="بحث..."
        bind:value={searchQuery}
      />
    </div>

    <div class="flex items-center gap-1 rounded-lg border p-1">
      <button
        class="rounded-md px-3 py-1 text-sm {!filterType ? 'bg-muted font-medium' : 'text-muted-foreground'}"
        onclick={() => (filterType = '')}
      >
        الكل
      </button>
      <button
        class="rounded-md px-3 py-1 text-sm {filterType === 'expense' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 font-medium' : 'text-muted-foreground'}"
        onclick={() => (filterType = 'expense')}
      >
        مصروفات
      </button>
      <button
        class="rounded-md px-3 py-1 text-sm {filterType === 'income' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 font-medium' : 'text-muted-foreground'}"
        onclick={() => (filterType = 'income')}
      >
        إيرادات
      </button>
    </div>

    <div class="min-w-[160px]">
      <Select.Root bind:value={filterCategory}>
        <Select.Trigger class="w-full justify-between">
          {#if filterCategory}
            {@const cat = allCategories.find((c) => c.id === parseInt(filterCategory))}
            {cat?.name ?? 'كل الفئات'}
          {:else}
            <span class="text-muted-foreground">كل الفئات</span>
          {/if}
        </Select.Trigger>
        <Select.Content>
          {#each allCategories as cat (cat.id)}
            <Select.Item value={cat.id.toString()} label={cat.name} />
          {/each}
        </Select.Content>
      </Select.Root>
    </div>
  </div>

  <div class="rounded-xl border">
    <Table.Root>
      <Table.Header>
        <Table.Row>
          <Table.Head class="text-right">التاريخ</Table.Head>
          <Table.Head class="text-right">الوصف</Table.Head>
          <Table.Head class="text-right">الفئة</Table.Head>
          <Table.Head class="text-right">النوع</Table.Head>
          <Table.Head class="text-right">المبلغ</Table.Head>
        </Table.Row>
      </Table.Header>
      <Table.Body>
        {#each filteredTransactions as tx (tx.id)}
          <Table.Row>
            <Table.Cell class="text-right text-muted-foreground">{tx.date}</Table.Cell>
            <Table.Cell class="text-right font-medium">{tx.description}</Table.Cell>
            <Table.Cell class="text-right text-muted-foreground">{tx.category?.name ?? '-'}</Table.Cell>
            <Table.Cell class="text-right">
              <span
                class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium {tx.type === 'expense'
                  ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                  : 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'}"
              >
                {#if tx.type === 'expense'}
                  <ArrowDownRight class="size-3" />
                  مصروف
                {:else}
                  <ArrowUpRight class="size-3" />
                  دخل
                {/if}
              </span>
            </Table.Cell>
            <Table.Cell class="text-right tabular-nums font-semibold {tx.type === 'expense'
              ? 'text-red-600 dark:text-red-400'
              : 'text-green-600 dark:text-green-400'}">
              {tx.type === 'expense' ? '-' : '+'}{tx.amount.toLocaleString('ar-SA')} ر.س
            </Table.Cell>
          </Table.Row>
        {:else}
          <Table.Row>
            <Table.Cell colspan="5" class="text-center text-muted-foreground py-8">
              لا توجد معاملات
            </Table.Cell>
          </Table.Row>
        {/each}
      </Table.Body>
    </Table.Root>
  </div>

  {#if filteredTransactions.length > 0}
    <div class="flex items-center justify-between">
      <p class="text-sm text-muted-foreground">
        {filteredTransactions.length} معاملة
      </p>
      <div class="flex items-center gap-1">
        <Button variant="outline" size="sm" disabled>السابق</Button>
        <Button variant="outline" size="sm" disabled>التالي</Button>
      </div>
    </div>
  {/if}
</div>

<AddTransactionDrawer bind:open={drawerOpen} onAdd={handleAdd} />
