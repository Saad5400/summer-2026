<script lang="ts">
  import { useForm } from '@inertiajs/svelte';
  import Plus from 'lucide-svelte/icons/plus';
  import Save from 'lucide-svelte/icons/save';
  import X from 'lucide-svelte/icons/x';
  import { fly } from 'svelte/transition';
  import { Button } from '@/components/ui/button';
  import { Input } from '@/components/ui/input';
  import { Label } from '@/components/ui/label';
  import { Spinner } from '@/components/ui/spinner';
  import { store, update } from '@/routes/transactions';
  import type { Category, Transaction, TransactionType } from '@/types';

  let {
    open = $bindable(false),
    onOpenChange,
    editTransaction = null,
    categories = [],
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

  function selectCategory(cat: Category) {
    form.category_id = String(cat.id);
    form.clearErrors('category_id');
  }

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
</script>

<svelte:window onkeydown={handleKeydown} />

{#if open}
  <div class="fixed inset-0 z-50">
    <button
      type="button"
      class="fixed inset-0 bg-black/50"
      aria-label="إغلاق"
      onclick={close}
    ></button>
    <div
      class="fixed inset-y-0 end-0 w-full max-w-sm flex flex-col gap-5 overflow-y-auto border-s bg-background p-6 shadow-lg"
      in:fly={{ x: 320, duration: 260, opacity: 1 }}
    >
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold">
          {#if isEditing}
            {editTransaction.type === 'expense' ? 'تعديل مصروف' : 'تعديل دخل'}
          {:else if transactionType === 'expense'}
            إضافة مصروف
          {:else}
            إضافة دخل
          {/if}
        </h2>
        <button
          type="button"
          class="rounded-sm opacity-70 hover:opacity-100"
          aria-label="إغلاق"
          onclick={close}
        >
          <X class="size-4" />
        </button>
      </div>

      <div class="flex rounded-lg border p-1">
        <button
          class="flex-1 rounded-md px-3 py-1.5 text-sm font-medium transition-colors {transactionType === 'expense'
            ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
            : 'text-muted-foreground hover:text-foreground'}"
          onclick={() => handleTypeChange('expense')}
        >
          مصروف
        </button>
        <button
          class="flex-1 rounded-md px-3 py-1.5 text-sm font-medium transition-colors {transactionType === 'income'
            ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
            : 'text-muted-foreground hover:text-foreground'}"
          onclick={() => handleTypeChange('income')}
        >
          دخل
        </button>
      </div>

      <div class="flex flex-col gap-4">
        <div class="space-y-1.5">
          <Label for="add-amount">المبلغ (ر.س) <span class="text-destructive">*</span></Label>
          <Input
            id="add-amount"
            type="number"
            placeholder="0.00"
            step="0.01"
            min="0"
            bind:value={form.amount}
          />
          {#if form.errors.amount}
            <p class="text-xs text-destructive">{form.errors.amount}</p>
          {/if}
        </div>

        <div class="space-y-1.5">
          <Label for="add-category">الفئة <span class="text-destructive">*</span></Label>

          <div class="grid grid-cols-2 gap-1.5">
            {#each filteredCategories as cat (cat.id)}
              <button
                type="button"
                class="flex items-center gap-2 rounded-lg border px-3 py-2.5 text-sm font-medium transition-all hover:scale-[1.02] active:scale-[0.98] {form.category_id === String(cat.id)
                  ? 'ring-2 ring-foreground ring-offset-1 bg-muted'
                  : 'hover:bg-muted/50'}"
                style="{form.category_id === String(cat.id) ? 'border-color: ' + (cat.color ?? '#6b7280') + ';' : ''}"
                onclick={() => selectCategory(cat)}
              >
                <span
                  class="size-3 shrink-0 rounded-full"
                  style="background-color: {cat.color ?? '#6b7280'}"
                ></span>
                <span class="truncate">{cat.name}</span>
              </button>
            {/each}
          </div>

          {#if form.errors.category_id}
            <p class="text-xs text-destructive">{form.errors.category_id}</p>
          {/if}
        </div>

        <div class="space-y-1.5">
          <Label for="add-description">الوصف <span class="text-xs text-muted-foreground">(اختياري)</span></Label>
          <Input
            id="add-description"
            type="text"
            placeholder="اختياري ..."
            bind:value={form.description}
          />
          {#if form.errors.description}
            <p class="text-xs text-destructive">{form.errors.description}</p>
          {/if}
        </div>

        <div class="space-y-1.5">
          <Label for="add-date">التاريخ</Label>
          <Input id="add-date" type="date" bind:value={form.date} />
          {#if form.errors.date}
            <p class="text-xs text-destructive">{form.errors.date}</p>
          {/if}
        </div>
      </div>

      <div class="mt-auto flex gap-2 pt-4">
        <Button
          variant="outline"
          class="flex-1"
          onclick={close}
          disabled={form.processing}
        >
          إلغاء
        </Button>
        <Button
          class="flex-1 {transactionType === 'expense' ? 'bg-red-600 hover:bg-red-700' : ''}"
          onclick={handleSubmit}
          disabled={form.processing || !form.amount || !form.category_id}
        >
          {#if form.processing}
            <Spinner class="size-4" />
            جاري الحفظ...
          {:else if isEditing}
            <Save class="size-4" />
            حفظ
          {:else}
            <Plus class="size-4" />
            إضافة
          {/if}
        </Button>
      </div>
    </div>
  </div>
{/if}
