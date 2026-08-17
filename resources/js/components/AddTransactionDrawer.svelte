<script lang="ts">
    import { useForm } from '@inertiajs/svelte';
    import Plus from 'lucide-svelte/icons/plus';
    import Save from 'lucide-svelte/icons/save';
    import X from 'lucide-svelte/icons/x';
    import Check from 'lucide-svelte/icons/check';
    import ArrowDownLeft from 'lucide-svelte/icons/arrow-down-left';
    import ArrowUpRight from 'lucide-svelte/icons/arrow-up-right';
    import { fade, fly } from 'svelte/transition';
    import { cubicOut } from 'svelte/easing';
    import CategoryIcon from '@/components/CategoryIcon.svelte';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Spinner } from '@/components/ui/spinner';
    import { store, update } from '@/routes/transactions';
    import { cn } from '@/lib/utils';
    import type { Category, Transaction, TransactionType } from '@/types';

    let {
        open = $bindable(false),
        onOpenChange,
        editTransaction = null,
        categories = [],
        recentCategories = [],
        onSuccess,
    }: {
        open?: boolean;
        onOpenChange?: () => void;
        editTransaction?: Transaction | null;
        categories?: Category[];
        recentCategories?: Category[];
        onSuccess?: () => void;
    } = $props();

    const isEditing = $derived(editTransaction !== null);

    let transactionType: TransactionType = $state('expense');
    let didInit = $state(false);
    let isDesktop = $state(false);

    const form = useForm({
        amount: '',
        description: '',
        date: new Date().toISOString().slice(0, 10),
        category_id: '',
        type: 'expense' as string,
    });

    const filteredCategories = $derived(
        categories.filter((c) => c.type === transactionType),
    );

    const recentFiltered = $derived(
        recentCategories.filter((c) => c.type === transactionType).slice(0, 4),
    );

    const selectedCategory = $derived(
        categories.find((c) => String(c.id) === form.category_id) ?? null,
    );

    function selectCategory(cat: Category) {
        form.category_id = String(cat.id);
        form.clearErrors('category_id');
    }

    $effect(() => {
        const mq = window.matchMedia('(min-width: 640px)');
        const apply = () => (isDesktop = mq.matches);
        apply();
        mq.addEventListener('change', apply);

        return () => mq.removeEventListener('change', apply);
    });

    $effect(() => {
        if (!open) {
            didInit = false;

            return;
        }

        if (didInit) {
            form.category_id = '';

            return;
        }

        didInit = true;

        if (editTransaction) {
            transactionType = editTransaction.type;
            form.amount = String(editTransaction.amount);
            form.description = editTransaction.description;
            form.date = editTransaction.date;
            form.category_id = editTransaction.category_id
                ? String(editTransaction.category_id)
                : '';
            form.type = editTransaction.type;
        } else {
            transactionType = 'expense';
            form.reset();
        }

        form.clearErrors();
    });

    function close() {
        open = false;
        onOpenChange?.();
    }

    function handleSubmit() {
        if (!form.amount || !form.category_id || form.processing) {
            return;
        }

        form.type = transactionType;
        form.transform((data) => ({
            ...data,
            amount: parseFloat(data.amount),
            description: data.description || null,
            category_id: parseInt(data.category_id),
        }));

        if (isEditing && editTransaction) {
            form.put(update.url(editTransaction.id), {
                preserveScroll: true,
                onSuccess: () => {
                    close();
                    onSuccess?.();
                },
            });
        } else {
            form.post(store.url(), {
                preserveScroll: true,
                onSuccess: () => {
                    close();
                    onSuccess?.();
                },
            });
        }
    }

    function handleTypeChange(type: TransactionType) {
        transactionType = type;
        form.type = type;
        form.category_id = '';
    }

    function handleKeydown(e: KeyboardEvent) {
        if (e.key === 'Escape') {
            close();
        }
    }

    const canSubmit = $derived(
        !form.processing && !!form.amount && !!form.category_id,
    );
</script>

<svelte:window onkeydown={handleKeydown} />

{#if open}
    <div class="fixed inset-0 z-50">
        <button
            type="button"
            class="fixed inset-0 bg-foreground/40 backdrop-blur-[2px]"
            aria-label="إغلاق"
            onclick={close}
            transition:fade={{ duration: 200 }}
        ></button>

        <div
            class="fixed inset-x-0 bottom-0 flex max-h-[92vh] flex-col overflow-hidden rounded-t-2xl border-t bg-background shadow-elevated sm:inset-y-0 sm:end-0 sm:bottom-auto sm:inset-x-auto sm:max-h-none sm:w-full sm:max-w-md sm:rounded-none sm:rounded-s-2xl sm:border-s sm:border-t-0"
            in:fly={isDesktop
                ? { x: -32, duration: 280, opacity: 0, easing: cubicOut }
                : { y: 560, duration: 360, opacity: 1, easing: cubicOut }}
        >
            <!-- Grab handle (mobile) -->
            <div class="flex shrink-0 justify-center pt-3 sm:hidden">
                <span class="h-1.5 w-10 rounded-full bg-muted-foreground/25"
                ></span>
            </div>

            <!-- Header -->
            <div
                class="flex shrink-0 items-center justify-between gap-3 px-5 pt-4 pb-3 sm:pt-6"
            >
                <div class="flex items-center gap-3">
                    <span
                        class={cn(
                            'flex size-9 items-center justify-center rounded-xl transition-colors',
                            transactionType === 'expense'
                                ? 'bg-expense-muted text-expense'
                                : 'bg-income-muted text-income',
                        )}
                    >
                        {#if transactionType === 'expense'}
                            <ArrowDownLeft class="size-5" />
                        {:else}
                            <ArrowUpRight class="size-5" />
                        {/if}
                    </span>
                    <div>
                        <h2 class="text-base font-semibold leading-tight">
                            {#if isEditing}
                                تعديل معاملة
                            {:else if transactionType === 'expense'}
                                إضافة مصروف
                            {:else}
                                إضافة دخل
                            {/if}
                        </h2>
                        <p class="text-xs text-muted-foreground">
                            {transactionType === 'expense'
                                ? 'سجّل مصروفاً جديداً'
                                : 'سجّل دخلاً جديداً'}
                        </p>
                    </div>
                </div>
                <button
                    type="button"
                    class="flex size-8 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-accent hover:text-foreground active:scale-95"
                    aria-label="إغلاق"
                    onclick={close}
                >
                    <X class="size-4" />
                </button>
            </div>

            <!-- Scrollable body -->
            <div class="flex-1 overflow-y-auto px-5 pb-5">
                <!-- Type toggle -->
                <div class="grid grid-cols-2 gap-2 rounded-xl bg-muted p-1">
                    <button
                        type="button"
                        class={cn(
                            'flex items-center justify-center gap-1.5 rounded-lg py-2 text-sm font-medium transition-all duration-200 active:scale-[0.98]',
                            transactionType === 'expense'
                                ? 'bg-expense text-expense-foreground shadow-soft'
                                : 'text-muted-foreground hover:text-foreground',
                        )}
                        onclick={() => handleTypeChange('expense')}
                    >
                        <ArrowDownLeft class="size-4" />
                        مصروف
                    </button>
                    <button
                        type="button"
                        class={cn(
                            'flex items-center justify-center gap-1.5 rounded-lg py-2 text-sm font-medium transition-all duration-200 active:scale-[0.98]',
                            transactionType === 'income'
                                ? 'bg-income text-income-foreground shadow-soft'
                                : 'text-muted-foreground hover:text-foreground',
                        )}
                        onclick={() => handleTypeChange('income')}
                    >
                        <ArrowUpRight class="size-4" />
                        دخل
                    </button>
                </div>

                <!-- Big amount -->
                <div class="mt-5">
                    <div
                        class={cn(
                            'flex flex-col items-center rounded-2xl border bg-card px-4 py-5 transition-colors focus-within:ring-2',
                            transactionType === 'expense'
                                ? 'focus-within:border-expense focus-within:ring-expense/20'
                                : 'focus-within:border-income focus-within:ring-income/20',
                        )}
                    >
                        <span class="text-xs font-medium text-muted-foreground"
                            >المبلغ</span
                        >
                        <div class="mt-1 flex items-baseline gap-1.5">
                            <input
                                id="add-amount"
                                type="number"
                                inputmode="decimal"
                                placeholder="0"
                                step="0.01"
                                min="0"
                                dir="ltr"
                                bind:value={form.amount}
                                class={cn(
                                    'w-full min-w-0 max-w-[8ch] border-0 bg-transparent p-0 text-center text-4xl font-bold tabular-nums tracking-tight outline-none placeholder:text-muted-foreground/40 focus:ring-0',
                                    transactionType === 'expense'
                                        ? 'text-expense'
                                        : 'text-income',
                                )}
                            />
                            <span
                                class="shrink-0 text-lg font-semibold text-muted-foreground"
                                >ر.س</span
                            >
                        </div>
                    </div>
                    {#if form.errors.amount}
                        <p class="mt-1.5 text-center text-xs text-destructive">
                            {form.errors.amount}
                        </p>
                    {/if}
                </div>

                <!-- Recent quick pick -->
                {#if recentFiltered.length > 0}
                    <div class="mt-5">
                        <p
                            class="mb-2 text-xs font-medium text-muted-foreground"
                        >
                            الأكثر استخداماً
                        </p>
                        <div class="-mx-1 flex gap-2 overflow-x-auto px-1 pb-1">
                            {#each recentFiltered as cat (cat.id)}
                                <button
                                    type="button"
                                    onclick={() => selectCategory(cat)}
                                    class={cn(
                                        'flex shrink-0 items-center gap-2 rounded-full border py-1.5 pe-3 ps-1.5 text-sm font-medium transition-all duration-200 active:scale-95',
                                        form.category_id === String(cat.id)
                                            ? 'border-primary bg-primary/5 text-foreground ring-1 ring-primary'
                                            : 'border-border text-muted-foreground hover:bg-accent hover:text-foreground',
                                    )}
                                >
                                    <CategoryIcon
                                        icon={cat.icon}
                                        color={cat.color}
                                        size="sm"
                                    />
                                    {cat.name}
                                </button>
                            {/each}
                        </div>
                    </div>
                {/if}

                <!-- Category grid -->
                <div class="mt-5">
                    <div class="mb-2 flex items-center justify-between">
                        <Label
                            class="text-xs font-medium text-muted-foreground"
                        >
                            الفئة <span class="text-destructive">*</span>
                        </Label>
                        {#if selectedCategory}
                            <span class="text-xs font-medium text-foreground"
                                >{selectedCategory.name}</span
                            >
                        {/if}
                    </div>

                    <div class="grid grid-cols-4 gap-2">
                        {#each filteredCategories as cat (cat.id)}
                            {@const active =
                                form.category_id === String(cat.id)}
                            <button
                                type="button"
                                onclick={() => selectCategory(cat)}
                                class={cn(
                                    'relative flex flex-col items-center gap-1.5 rounded-xl border p-2.5 transition-all duration-200 active:scale-95',
                                    active
                                        ? 'border-primary bg-primary/5 ring-2 ring-primary'
                                        : 'border-border hover:border-border hover:bg-accent',
                                )}
                            >
                                {#if active}
                                    <span
                                        class="absolute -top-1.5 -end-1.5 flex size-4 items-center justify-center rounded-full bg-primary text-primary-foreground"
                                    >
                                        <Check class="size-2.5" />
                                    </span>
                                {/if}
                                <CategoryIcon
                                    icon={cat.icon}
                                    color={cat.color}
                                    size="md"
                                />
                                <span
                                    class="w-full truncate text-center text-[11px] font-medium leading-tight"
                                >
                                    {cat.name}
                                </span>
                            </button>
                        {/each}
                    </div>

                    {#if filteredCategories.length === 0}
                        <p
                            class="rounded-xl border border-dashed py-6 text-center text-xs text-muted-foreground"
                        >
                            لا توجد فئات لهذا النوع
                        </p>
                    {/if}
                    {#if form.errors.category_id}
                        <p class="mt-1.5 text-xs text-destructive">
                            {form.errors.category_id}
                        </p>
                    {/if}
                </div>

                <!-- Description -->
                <div class="mt-5 space-y-1.5">
                    <Label
                        for="add-description"
                        class="text-xs font-medium text-muted-foreground"
                    >
                        الوصف <span class="font-normal">(اختياري)</span>
                    </Label>
                    <Input
                        id="add-description"
                        type="text"
                        placeholder="مثال: قهوة الصباح"
                        bind:value={form.description}
                    />
                    {#if form.errors.description}
                        <p class="text-xs text-destructive">
                            {form.errors.description}
                        </p>
                    {/if}
                </div>

                <!-- Date -->
                <div class="mt-4 space-y-1.5">
                    <Label
                        for="add-date"
                        class="text-xs font-medium text-muted-foreground"
                        >التاريخ</Label
                    >
                    <Input id="add-date" type="date" bind:value={form.date} />
                    {#if form.errors.date}
                        <p class="text-xs text-destructive">
                            {form.errors.date}
                        </p>
                    {/if}
                </div>
            </div>

            <!-- Footer -->
            <div
                class="flex shrink-0 gap-2 border-t bg-background px-5 py-4 pb-[max(1rem,env(safe-area-inset-bottom))]"
            >
                <Button
                    variant="outline"
                    class="flex-1"
                    onclick={close}
                    disabled={form.processing}
                >
                    إلغاء
                </Button>
                <Button
                    class={cn(
                        'flex-[1.4]',
                        transactionType === 'expense'
                            ? 'bg-expense text-expense-foreground hover:bg-expense/90'
                            : 'bg-income text-income-foreground hover:bg-income/90',
                    )}
                    onclick={handleSubmit}
                    disabled={!canSubmit}
                >
                    {#if form.processing}
                        <Spinner class="size-4" />
                        جاري الحفظ...
                    {:else if isEditing}
                        <Save class="size-4" />
                        حفظ التعديلات
                    {:else}
                        <Plus class="size-4" />
                        إضافة
                    {/if}
                </Button>
            </div>
        </div>
    </div>
{/if}
