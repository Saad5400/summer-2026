<script lang="ts">
  import { fly } from 'svelte/transition';
  import X from 'lucide-svelte/icons/x';
  import Plus from 'lucide-svelte/icons/plus';
  import { Button } from '@/components/ui/button';
  import { Input } from '@/components/ui/input';
  import { Label } from '@/components/ui/label';
  import * as Select from '@/components/ui/select';
  import { EXPENSE_CATEGORIES, INCOME_CATEGORIES } from '@/types';
  import type { TransactionType, TransactionFormData } from '@/types';

  let {
    open = $bindable(false),
    onAdd,
  }: {
    open?: boolean;
    onAdd?: (data: TransactionFormData) => void;
  } = $props();

  let transactionType: TransactionType = $state('expense');
  let amount = $state('');
  let description = $state('');
  let date = $state(new Date().toISOString().slice(0, 10));
  let categoryId = $state('');

  let submitting = $state(false);

  const categories = $derived(
    transactionType === 'expense' ? EXPENSE_CATEGORIES : INCOME_CATEGORIES,
  );

  $effect(() => {
    categoryId = '';
  });

  function close() {
    open = false;
    resetForm();
  }

  function resetForm() {
    transactionType = 'expense';
    amount = '';
    description = '';
    date = new Date().toISOString().slice(0, 10);
    categoryId = '';
    submitting = false;
  }

  function handleSubmit() {
    if (!amount || !description) return;
    submitting = true;

    onAdd?.({
      amount: parseFloat(amount),
      description,
      date,
      type: transactionType,
      category_id: categoryId ? parseInt(categoryId) : null,
    });

    setTimeout(() => {
      close();
    }, 300);
  }

  function handleKeydown(e: KeyboardEvent) {
    if (e.key === 'Escape') close();
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
      class="fixed inset-y-0 right-0 w-full max-w-sm flex flex-col gap-5 overflow-y-auto border-l bg-background p-6 shadow-lg"
      in:fly={{ x: 320, duration: 260, opacity: 1 }}
    >
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold">
          {transactionType === 'expense' ? 'إضافة مصروف' : 'إضافة دخل'}
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
          onclick={() => (transactionType = 'expense')}
        >
          مصروف
        </button>
        <button
          class="flex-1 rounded-md px-3 py-1.5 text-sm font-medium transition-colors {transactionType === 'income'
            ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
            : 'text-muted-foreground hover:text-foreground'}"
          onclick={() => (transactionType = 'income')}
        >
          دخل
        </button>
      </div>

      <div class="flex flex-col gap-4">
        <div class="space-y-1.5">
          <Label>المبلغ (ر.س)</Label>
          <Input
            id="add-amount"
            type="number"
            placeholder="0.00"
            step="0.01"
            min="0"
            bind:value={amount}
          />
        </div>

        <div class="space-y-1.5">
          <Label>الوصف</Label>
          <Input
            id="add-description"
            type="text"
            placeholder="مثال: مشتريات بقالة"
            bind:value={description}
          />
        </div>

        <div class="space-y-1.5">
          <Label>الفئة</Label>
          <Select.Root bind:value={categoryId}>
            <Select.Trigger id="add-category" class="w-full justify-between">
              {#if categoryId}
                {@const cat = categories.find((c) => c.id === parseInt(categoryId))}
                {cat?.name ?? 'اختر الفئة'}
              {:else}
                <span class="text-muted-foreground">اختر الفئة</span>
              {/if}
            </Select.Trigger>
            <Select.Content>
              {#each categories as cat (cat.id)}
                <Select.Item value={cat.id.toString()} label={cat.name} />
              {/each}
            </Select.Content>
          </Select.Root>
        </div>

        <div class="space-y-1.5">
          <Label>التاريخ</Label>
          <Input id="add-date" type="date" bind:value={date} />
        </div>
      </div>

      <div class="mt-auto flex gap-2 pt-4">
        <Button
          variant="outline"
          class="flex-1"
          onclick={close}
          disabled={submitting}
        >
          إلغاء
        </Button>
        <Button
          class="flex-1 {transactionType === 'expense' ? 'bg-red-600 hover:bg-red-700' : ''}"
          onclick={handleSubmit}
          disabled={submitting || !amount || !description}
        >
          <Plus class="size-4" />
          إضافة
        </Button>
      </div>
    </div>
  </div>
{/if}
