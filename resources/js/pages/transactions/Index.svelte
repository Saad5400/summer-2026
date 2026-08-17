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
    import ChevronLeft from 'lucide-svelte/icons/chevron-left';
    import ChevronRight from 'lucide-svelte/icons/chevron-right';
    import Plus from 'lucide-svelte/icons/plus';
    import Search from 'lucide-svelte/icons/search';
    import X from 'lucide-svelte/icons/x';
    import Receipt from 'lucide-svelte/icons/receipt';
    import AddTransactionDrawer from '@/components/AddTransactionDrawer.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import TransactionRow from '@/components/transactions/TransactionRow.svelte';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import { Skeleton } from '@/components/ui/skeleton';
    import * as Select from '@/components/ui/select';
    import * as Tabs from '@/components/ui/tabs';
    import * as AlertDialog from '@/components/ui/alert-dialog';
    import * as Empty from '@/components/ui/empty';
    import { index as transactionsIndex, destroy } from '@/routes/transactions';
    import { formatRelativeDate, formatMoney } from '@/lib/format';
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
    const transactions = $derived(paginator.data);
    const isLoading = $derived(!paginator || !paginator.data);

    let drawerOpen = $state(false);
    let searchInput = $state(page.props.filters.search ?? '');
    let filterType = $state(page.props.filters.type ?? '');
    let filterCategory = $state(page.props.filters.category_id ?? '');
    let deleteTarget = $state<Transaction | null>(null);
    let editTransaction = $state<Transaction | null>(null);
    let perPage = $state(String(page.props.transactions?.per_page ?? '15'));

    let searchTimer: ReturnType<typeof setTimeout> | null = null;

    const hasActiveFilters = $derived(
        !!searchInput || !!filterType || !!filterCategory,
    );

    // Group transactions by day, preserving server order.
    const groups = $derived.by(() => {
        const map = new Map<string, Transaction[]>();

        for (const tx of transactions) {
            const list = map.get(tx.date);

            if (list) {
                list.push(tx);
            } else {
                map.set(tx.date, [tx]);
            }
        }

        return Array.from(map, ([date, items]) => ({ date, items }));
    });

    function groupNet(items: Transaction[]): number {
        return items.reduce(
            (sum, t) => sum + (t.type === 'income' ? t.amount : -t.amount),
            0,
        );
    }

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

    function clearSearch() {
        searchInput = '';
        navigate({ search: '' });
    }

    function handleTypeChange(type: string) {
        filterType = type;
        navigate({ type });
    }

    function handleCategoryChange(categoryId: string) {
        filterCategory = categoryId;
        navigate({ category_id: categoryId });
    }

    function clearAllFilters() {
        searchInput = '';
        filterType = '';
        filterCategory = '';
        navigate({ search: '', type: '', category_id: '' });
    }

    function goToPage(pageNum: number) {
        navigate({ page: String(pageNum) });
    }

    function handlePerPageChange(val: string | null) {
        perPage = val ?? '15';
        navigate({ per_page: perPage, page: '1' });
    }

    function requestDelete(tx: Transaction) {
        deleteTarget = tx;
    }

    function executeDelete() {
        const target = deleteTarget;

        if (!target) {
            return;
        }

        router.delete(destroy.url(target.id), {
            preserveScroll: true,
            onSuccess: () => {
                deleteTarget = null;
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

    const selectedCategoryName = $derived(
        filterCategory
            ? (categories.find((c) => c.id === parseInt(filterCategory))
                  ?.name ?? null)
            : null,
    );
</script>

<AppHead title="المعاملات" />

<div class="flex flex-1 flex-col gap-6 px-4 py-6 md:px-6 md:py-8">
    <!-- Header -->
    <div class="flex items-start justify-between gap-3">
        <div class="space-y-1">
            <div class="flex items-center gap-2.5">
                <h1 class="text-xl font-semibold tracking-tight md:text-2xl">
                    المعاملات
                </h1>
                {#if !isLoading}
                    <span
                        class="rounded-full bg-muted px-2 py-0.5 text-xs font-medium tabular-nums text-muted-foreground"
                    >
                        {paginator.total}
                    </span>
                {/if}
            </div>
            <p class="text-sm text-muted-foreground">
                جميع مصروفاتك وإيراداتك في مكان واحد
            </p>
        </div>

        <Button
            class="shadow-soft active:scale-95"
            onclick={() => (drawerOpen = true)}
        >
            <Plus class="size-4" />
            <span class="hidden sm:inline">إضافة معاملة</span>
            <span class="sm:hidden">إضافة</span>
        </Button>
    </div>

    <!-- Filter / search bar -->
    <div
        class="sticky top-0 z-20 -mx-4 flex flex-col gap-3 border-b border-border/60 bg-background/85 px-4 pb-3 pt-1 backdrop-blur-md md:-mx-6 md:px-6"
    >
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <!-- Search -->
            <div class="relative flex-1">
                <Search
                    class="pointer-events-none absolute start-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    class="h-10 ps-9 pe-9"
                    placeholder="ابحث في المعاملات..."
                    value={searchInput}
                    oninput={handleSearchInput}
                />
                {#if searchInput}
                    <button
                        type="button"
                        class="absolute end-2.5 top-1/2 flex size-5 -translate-y-1/2 items-center justify-center rounded-full text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                        aria-label="مسح البحث"
                        onclick={clearSearch}
                    >
                        <X class="size-3.5" />
                    </button>
                {/if}
            </div>

            <!-- Category filter -->
            <div class="sm:w-44">
                <Select.Root
                    value={filterCategory}
                    onValueChange={(val) => handleCategoryChange(val ?? '')}
                >
                    <Select.Trigger class="h-10 w-full justify-between">
                        {#if selectedCategoryName}
                            {selectedCategoryName}
                        {:else}
                            <span class="text-muted-foreground">كل الفئات</span>
                        {/if}
                    </Select.Trigger>
                    <Select.Content>
                        {#each categories as cat (cat.id)}
                            <Select.Item
                                value={cat.id.toString()}
                                label={cat.name}
                            />
                        {/each}
                    </Select.Content>
                </Select.Root>
            </div>
        </div>

        <!-- Type segmented control -->
        <div class="flex items-center gap-2">
            <Tabs.Root
                value={filterType || 'all'}
                onValueChange={(v) => handleTypeChange(v === 'all' ? '' : v)}
                class="flex-1"
            >
                <Tabs.List class="w-full">
                    <Tabs.Trigger value="all" class="flex-1">الكل</Tabs.Trigger>
                    <Tabs.Trigger
                        value="expense"
                        class="flex-1 data-active:text-expense"
                        >مصروفات</Tabs.Trigger
                    >
                    <Tabs.Trigger
                        value="income"
                        class="flex-1 data-active:text-income"
                        >إيرادات</Tabs.Trigger
                    >
                </Tabs.List>
            </Tabs.Root>

            {#if hasActiveFilters}
                <Button
                    variant="ghost"
                    size="sm"
                    class="shrink-0 text-muted-foreground"
                    onclick={clearAllFilters}
                >
                    <X class="size-3.5" />
                    مسح
                </Button>
            {/if}
        </div>
    </div>

    <!-- Content -->
    {#if isLoading}
        <div class="flex flex-col gap-6">
            {#each Array(2) as _, gi (gi)}
                <div class="space-y-2">
                    <Skeleton class="h-3 w-20" />
                    <div class="rounded-2xl border bg-card p-2">
                        {#each Array(4) as _, ri (ri)}
                            <div class="flex items-center gap-3 px-2.5 py-2.5">
                                <Skeleton class="size-10 rounded-xl" />
                                <div class="flex-1 space-y-2">
                                    <Skeleton class="h-3.5 w-32" />
                                    <Skeleton class="h-3 w-20" />
                                </div>
                                <Skeleton class="h-4 w-16" />
                            </div>
                        {/each}
                    </div>
                </div>
            {/each}
        </div>
    {:else if transactions.length === 0}
        <Empty.Root class="mt-4 py-16">
            <Empty.Header>
                <Empty.Media
                    variant="icon"
                    class="size-14 rounded-2xl bg-muted text-muted-foreground"
                >
                    <Receipt class="size-6" />
                </Empty.Media>
                <Empty.Title>
                    {hasActiveFilters
                        ? 'لا توجد نتائج مطابقة'
                        : 'لا توجد معاملات بعد'}
                </Empty.Title>
                <Empty.Description>
                    {hasActiveFilters
                        ? 'جرّب تعديل عوامل التصفية أو البحث بكلمات أخرى.'
                        : 'ابدأ بتسجيل أول مصروف أو دخل لتتبّع أموالك.'}
                </Empty.Description>
            </Empty.Header>
            <Empty.Content>
                {#if hasActiveFilters}
                    <Button variant="outline" onclick={clearAllFilters}>
                        <X class="size-4" />
                        مسح عوامل التصفية
                    </Button>
                {:else}
                    <Button onclick={() => (drawerOpen = true)}>
                        <Plus class="size-4" />
                        إضافة معاملة
                    </Button>
                {/if}
            </Empty.Content>
        </Empty.Root>
    {:else}
        <div class="flex flex-col gap-6">
            {#each groups as group (group.date)}
                {@const net = groupNet(group.items)}
                <section class="space-y-2">
                    <div class="flex items-center justify-between px-1">
                        <h2
                            class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                        >
                            {formatRelativeDate(group.date)}
                        </h2>
                        <span
                            class="text-xs font-medium tabular-nums text-muted-foreground"
                        >
                            {net >= 0 ? '+' : '−'}{formatMoney(Math.abs(net))}
                        </span>
                    </div>

                    <div class="rounded-2xl border bg-card p-1.5 shadow-soft">
                        {#each group.items as tx, i (tx.id)}
                            {#if i > 0}
                                <div class="mx-3 h-px bg-border/50"></div>
                            {/if}
                            <TransactionRow
                                transaction={tx}
                                style={`animation-delay: ${Math.min(i * 35, 300)}ms`}
                                onEdit={openEdit}
                                onDelete={requestDelete}
                            />
                        {/each}
                    </div>
                </section>
            {/each}
        </div>
    {/if}

    <!-- Pagination -->
    {#if !isLoading && paginator.total > 0}
        <div
            class="flex flex-col items-center justify-between gap-3 pt-1 sm:flex-row"
        >
            <p class="text-sm text-muted-foreground">
                عرض <span class="font-medium text-foreground tabular-nums"
                    >{paginator.from ?? 0}</span
                >–<span class="font-medium text-foreground tabular-nums"
                    >{paginator.to ?? 0}</span
                >
                من
                <span class="font-medium text-foreground tabular-nums"
                    >{paginator.total}</span
                > معاملة
            </p>

            <div class="flex items-center gap-2">
                <div class="flex items-center gap-1.5">
                    <span class="text-xs text-muted-foreground">لكل صفحة</span>
                    <Select.Root
                        value={perPage}
                        onValueChange={(val) => handlePerPageChange(val)}
                    >
                        <Select.Trigger
                            class="h-9 w-[68px] justify-between text-sm"
                        >
                            {perPage}
                        </Select.Trigger>
                        <Select.Content>
                            <Select.Item value="15" label="15" />
                            <Select.Item value="30" label="30" />
                            <Select.Item value="50" label="50" />
                        </Select.Content>
                    </Select.Root>
                </div>

                {#if paginator.last_page > 1}
                    <div class="flex items-center gap-1">
                        <Button
                            variant="outline"
                            size="icon"
                            class="size-9"
                            aria-label="السابق"
                            disabled={paginator.current_page <= 1}
                            onclick={() => goToPage(paginator.current_page - 1)}
                        >
                            <ChevronRight class="size-4" />
                        </Button>

                        {#each getPageNumbers() as pageNum (pageNum)}
                            <Button
                                variant={pageNum === paginator.current_page
                                    ? 'default'
                                    : 'outline'}
                                size="icon"
                                class="size-9 tabular-nums"
                                onclick={() => goToPage(pageNum)}
                            >
                                {pageNum}
                            </Button>
                        {/each}

                        <Button
                            variant="outline"
                            size="icon"
                            class="size-9"
                            aria-label="التالي"
                            disabled={paginator.current_page >=
                                paginator.last_page}
                            onclick={() => goToPage(paginator.current_page + 1)}
                        >
                            <ChevronLeft class="size-4" />
                        </Button>
                    </div>
                {/if}
            </div>
        </div>
    {/if}
</div>

<!-- Delete confirmation -->
<AlertDialog.Root
    open={deleteTarget !== null}
    onOpenChange={(v) => {
        if (!v) {
            deleteTarget = null;
        }
    }}
>
    <AlertDialog.Content>
        <AlertDialog.Header>
            <AlertDialog.Title>حذف المعاملة؟</AlertDialog.Title>
            <AlertDialog.Description>
                {#if deleteTarget}
                    سيتم حذف "{deleteTarget.description?.trim() ||
                        deleteTarget.category?.name ||
                        'هذه المعاملة'}" نهائياً. لا يمكن التراجع عن هذا
                    الإجراء.
                {/if}
            </AlertDialog.Description>
        </AlertDialog.Header>
        <AlertDialog.Footer>
            <AlertDialog.Cancel>إلغاء</AlertDialog.Cancel>
            <AlertDialog.Action
                class="bg-destructive text-white hover:bg-destructive/90"
                onclick={executeDelete}
            >
                حذف
            </AlertDialog.Action>
        </AlertDialog.Footer>
    </AlertDialog.Content>
</AlertDialog.Root>

<AddTransactionDrawer
    open={drawerOpen}
    onOpenChange={handleDrawerClose}
    {editTransaction}
    {categories}
    recentCategories={page.props.recentCategories ?? []}
    onSuccess={handleAddSuccess}
/>
