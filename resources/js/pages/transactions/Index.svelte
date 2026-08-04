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
  import type { PageProps as InertiaPageProps } from '@inertiajs/core';
  import { usePage, router } from '@inertiajs/svelte';
  import ArrowDownRight from 'lucide-svelte/icons/arrow-down-right';
  import ArrowUpRight from 'lucide-svelte/icons/arrow-up-right';
  import ChevronLeft from 'lucide-svelte/icons/chevron-left';
  import ChevronRight from 'lucide-svelte/icons/chevron-right';
  import Pencil from 'lucide-svelte/icons/pencil';
  import Plus from 'lucide-svelte/icons/plus';
  import Search from 'lucide-svelte/icons/search';
  import Trash from 'lucide-svelte/icons/trash';
  import AddTransactionDrawer from '@/components/AddTransactionDrawer.svelte';
  import AppHead from '@/components/AppHead.svelte';
  import Heading from '@/components/Heading.svelte';
  import { Button } from '@/components/ui/button';
  import { Input } from '@/components/ui/input';
  import { Skeleton } from '@/components/ui/skeleton';
  import * as Select from '@/components/ui/select';
  import * as Table from '@/components/ui/table';
  import { index as transactionsIndex, destroy } from '@/routes/transactions';
  import type { Category, Transaction } from '@/types';

  interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
  }

  interface PaginatedTransactions {
    data: Transaction[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: PaginationLink[];
    next_page_url: string | null;
    prev_page_url: string | null;
  }

  interface PageProps extends InertiaPageProps {
    transactions: PaginatedTransactions;
    categories: Category[];
    recentCategories: Category[];
    filters: {
      type: string | null;
      category_id: string | null;
      search: string | null;
      date_from: string | null;
      date_to: string | null;
    };
  }

  const page = usePage<PageProps>();
  const paginator = $derived(page.props.transactions);
  const categories = $derived(page.props.categories);
  const filters = $derived(page.props.filters);
  const transactions = $derived(paginator.data);
  const isLoading = $derived(!paginator || !paginator.data);

  let drawerOpen = $state(false);
  let searchInput = $state(page.props.filters.search ?? '');
  let filterType = $state(page.props.filters.type ?? '');
  let filterCategory = $state(page.props.filters.category_id ?? '');
  let deleteConfirmId = $state<number | null>(null);
  let editTransaction = $state<Transaction | null>(null);
  let perPage = $state(String(page.props.transactions?.per_page ?? '15'));

  let searchTimer: ReturnType<typeof setTimeout> | null = null;

  function navigate(params: Record<string, string>) {
    const query: Record<string, string> = {};

    if (filterType) {
query.type = filterType;
}

    if (filterCategory) {
query.category_id = filterCategory;
}

    if (searchInput) {
query.search = searchInput;
}

    if (perPage) {
query.per_page = perPage;
}

    for (const [key, value] of Object.entries(params)) {
      if (value) {
        query[key] = value;
      } else {
        delete query[key];
      }
    }

    router.get(transactionsIndex.url(), query, {
      preserveState: true,
      replace: true,
    });
  }

  function handleSearchInput(e: Event) {
    const target = e.target as HTMLInputElement;
    searchInput = target.value;

    if (searchTimer) {
clearTimeout(searchTimer);
}

    searchTimer = setTimeout(() => {
      navigate({ search: searchInput });
    }, 400);
  }

  function handleTypeChange(type: string) {
    filterType = type;
    navigate({ type });
  }

  function handleCategoryChange(categoryId: string) {
    filterCategory = categoryId;
    navigate({ category_id: categoryId });
  }

  function goToPage(pageNum: number) {
    navigate({ page: String(pageNum) });
  }

  function handlePerPageChange(val: string | null) {
    perPage = val ?? '15';
    navigate({ per_page: perPage, page: '1' });
  }

  function confirmDelete(id: number) {
    deleteConfirmId = id;
  }

  function cancelDelete() {
    deleteConfirmId = null;
  }

  function executeDelete(id: number) {
    router.delete(destroy.url(id), {
      preserveScroll: true,
      onSuccess: () => {
        deleteConfirmId = null;
        router.reload({ only: ['transactions'] });
      },
    });
  }

  function openEdit(tx: Transaction) {
    editTransaction = tx;
    drawerOpen = true;
  }

  function handleDrawerClose() {
    drawerOpen = false;
    editTransaction = null;
  }

  function handleAddSuccess() {
    drawerOpen = false;
    editTransaction = null;
    router.reload({ only: ['transactions'] });
  }

  function getPageNumbers(): number[] {
    const current = paginator.current_page;
    const last = paginator.last_page;
    const pages: number[] = [];
    const start = Math.max(1, current - 2);
    const end = Math.min(last, current + 2);

    for (let i = start; i <= end; i++) {
      pages.push(i);
    }

    return pages;
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
        value={searchInput}
        oninput={handleSearchInput}
      />
    </div>

    <div class="flex items-center gap-1 rounded-lg border p-1">
      <button
        class="rounded-md px-3 py-1 text-sm {!filterType ? 'bg-muted font-medium' : 'text-muted-foreground'}"
        onclick={() => handleTypeChange('')}
      >
        الكل
      </button>
      <button
        class="rounded-md px-3 py-1 text-sm {filterType === 'expense' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 font-medium' : 'text-muted-foreground'}"
        onclick={() => handleTypeChange('expense')}
      >
        مصروفات
      </button>
      <button
        class="rounded-md px-3 py-1 text-sm {filterType === 'income' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 font-medium' : 'text-muted-foreground'}"
        onclick={() => handleTypeChange('income')}
      >
        إيرادات
      </button>
    </div>

    <div class="min-w-[160px]">
      <Select.Root
        value={filterCategory}
        onValueChange={(val) => handleCategoryChange(val ?? '')}
      >
        <Select.Trigger class="w-full justify-between">
          {#if filterCategory}
            {@const cat = categories.find((c) => c.id === parseInt(filterCategory))}
            {cat?.name ?? 'كل الفئات'}
          {:else}
            <span class="text-muted-foreground">كل الفئات</span>
          {/if}
        </Select.Trigger>
        <Select.Content>
          {#each categories as cat (cat.id)}
            <Select.Item value={cat.id.toString()} label={cat.name} />
          {/each}
        </Select.Content>
      </Select.Root>
    </div>
  </div>

  {#if isLoading}
    <div class="rounded-xl border">
      <div class="hidden md:block overflow-x-auto">
        <Table.Root>
          <Table.Header>
            <Table.Row>
              <Table.Head class="text-right">التاريخ</Table.Head>
              <Table.Head class="text-right">الوصف</Table.Head>
              <Table.Head class="text-right">الفئة</Table.Head>
              <Table.Head class="text-right">النوع</Table.Head>
              <Table.Head class="text-right">المبلغ</Table.Head>
              <Table.Head class="text-right w-[80px]"></Table.Head>
            </Table.Row>
          </Table.Header>
          <Table.Body>
            {#each Array(8) as _}
              <Table.Row>
                <Table.Cell><Skeleton class="h-4 w-20" /></Table.Cell>
                <Table.Cell><Skeleton class="h-4 w-32" /></Table.Cell>
                <Table.Cell><Skeleton class="h-5 w-16 rounded-full" /></Table.Cell>
                <Table.Cell><Skeleton class="h-5 w-14 rounded-full" /></Table.Cell>
                <Table.Cell><Skeleton class="h-4 w-20" /></Table.Cell>
                <Table.Cell><Skeleton class="h-4 w-12" /></Table.Cell>
              </Table.Row>
            {/each}
          </Table.Body>
        </Table.Root>
      </div>
      <div class="md:hidden p-3 space-y-3">
        {#each Array(5) as _}
          <div class="rounded-xl border p-3 space-y-2">
            <div class="flex items-center justify-between">
              <Skeleton class="h-3 w-16" />
              <Skeleton class="h-5 w-14 rounded-full" />
            </div>
            <Skeleton class="h-4 w-32" />
            <div class="flex items-center justify-between">
              <Skeleton class="h-5 w-14 rounded-full" />
              <Skeleton class="h-4 w-20" />
            </div>
          </div>
        {/each}
      </div>
    </div>
  {:else}
    <!-- Desktop table -->
    <div class="hidden md:block overflow-x-auto rounded-xl border">
      <Table.Root>
        <Table.Header>
          <Table.Row>
            <Table.Head class="text-right">التاريخ</Table.Head>
            <Table.Head class="text-right">الوصف</Table.Head>
            <Table.Head class="text-right">الفئة</Table.Head>
            <Table.Head class="text-right">النوع</Table.Head>
            <Table.Head class="text-right">المبلغ</Table.Head>
            <Table.Head class="text-right w-[80px]"></Table.Head>
          </Table.Row>
        </Table.Header>
        <Table.Body>
          {#each transactions as tx (tx.id)}
            <Table.Row>
              <Table.Cell class="text-right text-muted-foreground">{tx.date}</Table.Cell>
              <Table.Cell class="text-right font-medium">{tx.description}</Table.Cell>
              <Table.Cell class="text-right">
                {#if tx.category}
                  <span
                    class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium"
                    style="background-color: {tx.category.color}20; color: {tx.category.color}"
                  >
                    {tx.category.name}
                  </span>
                {:else}
                  <span class="text-muted-foreground">-</span>
                {/if}
              </Table.Cell>
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
              <Table.Cell class="text-right">
                <div class="flex items-center justify-end gap-1">
                  {#if deleteConfirmId === tx.id}
                    <Button
                      variant="destructive"
                      size="sm"
                      onclick={() => executeDelete(tx.id)}
                    >
                      تأكيد
                    </Button>
                    <Button
                      variant="outline"
                      size="sm"
                      onclick={cancelDelete}
                    >
                      إلغاء
                    </Button>
                  {:else}
                    <button
                      class="rounded p-1.5 text-muted-foreground hover:bg-muted hover:text-foreground"
                      onclick={() => openEdit(tx)}
                      aria-label="تعديل"
                    >
                      <Pencil class="size-3.5" />
                    </button>
                    <button
                      class="rounded p-1.5 text-muted-foreground hover:bg-red-100 hover:text-red-600 dark:hover:bg-red-900/30 dark:hover:text-red-400"
                      onclick={() => confirmDelete(tx.id)}
                      aria-label="حذف"
                    >
                      <Trash class="size-3.5" />
                    </button>
                  {/if}
                </div>
              </Table.Cell>
            </Table.Row>
          {:else}
            <Table.Row>
              <Table.Cell colspan="6" class="text-center text-muted-foreground py-8">
                لا توجد معاملات
              </Table.Cell>
            </Table.Row>
          {/each}
        </Table.Body>
      </Table.Root>
    </div>

    <!-- Mobile cards -->
    <div class="md:hidden space-y-3">
      {#each transactions as tx (tx.id)}
        <div class="rounded-xl border p-3">
          <div class="flex items-start justify-between gap-2 mb-2">
            <div class="min-w-0 flex-1">
              <p class="text-sm font-medium truncate">{tx.description}</p>
              <p class="text-xs text-muted-foreground">{tx.date}</p>
            </div>
            <span
              class="tabular-nums text-sm font-semibold shrink-0 {tx.type === 'expense'
                ? 'text-red-600 dark:text-red-400'
                : 'text-green-600 dark:text-green-400'}"
            >
              {tx.type === 'expense' ? '-' : '+'}{tx.amount.toLocaleString('ar-SA')} ر.س
            </span>
          </div>
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              {#if tx.category}
                <span
                  class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium"
                  style="background-color: {tx.category.color}20; color: {tx.category.color}"
                >
                  {tx.category.name}
                </span>
              {:else}
                <span class="text-xs text-muted-foreground">-</span>
              {/if}
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
            </div>
            <div class="flex items-center gap-1">
              {#if deleteConfirmId === tx.id}
                <Button
                  variant="destructive"
                  size="sm"
                  onclick={() => executeDelete(tx.id)}
                >
                  تأكيد
                </Button>
                <Button
                  variant="outline"
                  size="sm"
                  onclick={cancelDelete}
                >
                  إلغاء
                </Button>
              {:else}
                <button
                  class="rounded p-1.5 text-muted-foreground hover:bg-muted hover:text-foreground"
                  onclick={() => openEdit(tx)}
                  aria-label="تعديل"
                >
                  <Pencil class="size-3.5" />
                </button>
                <button
                  class="rounded p-1.5 text-muted-foreground hover:bg-red-100 hover:text-red-600 dark:hover:bg-red-900/30 dark:hover:text-red-400"
                  onclick={() => confirmDelete(tx.id)}
                  aria-label="حذف"
                >
                  <Trash class="size-3.5" />
                </button>
              {/if}
            </div>
          </div>
        </div>
      {:else}
        <div class="flex h-32 items-center justify-center rounded-xl border">
          <p class="text-sm text-muted-foreground">لا توجد معاملات</p>
        </div>
      {/each}
    </div>
  {/if}

  {#if paginator.total > 0}
    <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
      <p class="text-sm text-muted-foreground">
        عرض {paginator.from ?? 0}-{paginator.to ?? 0} من إجمالي {paginator.total} معاملة
      </p>
      <div class="flex items-center gap-2">
        <div class="flex items-center gap-1.5">
          <span class="text-xs text-muted-foreground">لكل صفحة</span>
          <Select.Root
            value={perPage}
            onValueChange={(val) => handlePerPageChange(val)}
          >
            <Select.Trigger class="w-[70px] justify-between text-sm">
              {perPage}
            </Select.Trigger>
            <Select.Content>
              <Select.Item value="15" label="15" />
              <Select.Item value="30" label="30" />
              <Select.Item value="50" label="50" />
            </Select.Content>
          </Select.Root>
        </div>
        <div class="flex items-center gap-1">
          <Button
            variant="outline"
            size="sm"
            disabled={paginator.current_page <= 1}
            onclick={() => goToPage(paginator.current_page - 1)}
          >
            <ChevronRight class="size-4" />
            السابق
          </Button>

          {#each getPageNumbers() as pageNum (pageNum)}
            <Button
              variant={pageNum === paginator.current_page ? 'default' : 'outline'}
              size="sm"
              class="min-w-[2.25rem]"
              onclick={() => goToPage(pageNum)}
            >
              {pageNum}
            </Button>
          {/each}

          <Button
            variant="outline"
            size="sm"
            disabled={paginator.current_page >= paginator.last_page}
            onclick={() => goToPage(paginator.current_page + 1)}
          >
            التالي
            <ChevronLeft class="size-4" />
          </Button>
        </div>
      </div>
    </div>
  {/if}
</div>

<AddTransactionDrawer
  open={drawerOpen}
  onOpenChange={handleDrawerClose}
  editTransaction={editTransaction}
  {categories}
  recentCategories={page.props.recentCategories ?? []}
  onSuccess={handleAddSuccess}
/>
