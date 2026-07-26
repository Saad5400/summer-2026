<script module lang="ts">
  export const layout = {
    breadcrumbs: [
      {
        title: 'الفئات',
        href: '/categories',
      },
    ],
  };
</script>

<script lang="ts">
  import Check from 'lucide-svelte/icons/check';
  import Pencil from 'lucide-svelte/icons/pencil';
  import Plus from 'lucide-svelte/icons/plus';
  import Trash from 'lucide-svelte/icons/trash';
  import X from 'lucide-svelte/icons/x';
  import AppHead from '@/components/AppHead.svelte';
  import Heading from '@/components/Heading.svelte';
  import { Button } from '@/components/ui/button';
  import { Input } from '@/components/ui/input';
  import { EXPENSE_CATEGORIES, INCOME_CATEGORIES } from '@/types';
  import type { Category } from '@/types';

  const COLORS = [
    '#ef4444',
    '#f97316',
    '#eab308',
    '#22c55e',
    '#14b8a6',
    '#06b6d4',
    '#3b82f6',
    '#6366f1',
    '#8b5cf6',
    '#a855f7',
    '#d946ef',
    '#ec4899',
    '#f43f5e',
    '#6b7280',
  ];

  let expenseCategories = $state<Category[]>(
    EXPENSE_CATEGORIES.map((c) => ({ ...c })),
  );
  let incomeCategories = $state<Category[]>(
    INCOME_CATEGORIES.map((c) => ({ ...c })),
  );

  let activeTab = $state<'expense' | 'income'>('expense');
  let adding = $state(false);
  let editingId = $state<number | null>(null);
  let newName = $state('');
  let newColor = $state(COLORS[0]);
  let editName = $state('');
  let editColor = $state(COLORS[0]);
  let confirmDeleteId = $state<number | null>(null);
  let nextId = $state(100);

  const displayedCategories = $derived(
    activeTab === 'expense' ? expenseCategories : incomeCategories,
  );

  const activeType = $derived(activeTab);

  function startAdd() {
    adding = true;
    editingId = null;
    newName = '';
    newColor = COLORS[0];
    confirmDeleteId = null;
  }

  function cancelAdd() {
    adding = false;
    newName = '';
    newColor = COLORS[0];
  }

  function saveNew() {
    const trimmed = newName.trim();

    if (!trimmed) {
 return; 
}

    const cat: Category = {
      id: nextId++,
      name: trimmed,
      icon: '',
      color: newColor,
      type: activeType,
    };

    if (activeType === 'expense') {
      expenseCategories = [...expenseCategories, cat];
    } else {
      incomeCategories = [...incomeCategories, cat];
    }

    adding = false;
    newName = '';
    newColor = COLORS[0];
  }

  function startEdit(cat: Category) {
    editingId = cat.id;
    editName = cat.name;
    editColor = cat.color;
    adding = false;
    confirmDeleteId = null;
  }

  function cancelEdit() {
    editingId = null;
    editName = '';
    editColor = COLORS[0];
  }

  function saveEdit() {
    const trimmed = editName.trim();

    if (!trimmed || editingId === null) {
 return; 
}

    if (activeType === 'expense') {
      expenseCategories = expenseCategories.map((c) =>
        c.id === editingId
          ? { ...c, name: trimmed, color: editColor }
          : c,
      );
    } else {
      incomeCategories = incomeCategories.map((c) =>
        c.id === editingId
          ? { ...c, name: trimmed, color: editColor }
          : c,
      );
    }

    editingId = null;
    editName = '';
    editColor = COLORS[0];
  }

  function confirmDelete(cat: Category) {
    confirmDeleteId = cat.id;
    editingId = null;
    adding = false;
  }

  function cancelDelete() {
    confirmDeleteId = null;
  }

  function executeDelete() {
    if (confirmDeleteId === null) {
 return; 
}

    if (activeType === 'expense') {
      expenseCategories = expenseCategories.filter(
        (c) => c.id !== confirmDeleteId,
      );
    } else {
      incomeCategories = incomeCategories.filter(
        (c) => c.id !== confirmDeleteId,
      );
    }

    confirmDeleteId = null;
  }
</script>

<AppHead title="الفئات" />

<div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
  <div class="flex items-center justify-between">
    <Heading
      title="الفئات"
      description="إدارة فئات المصروفات والإيرادات"
    />
    <Button onclick={startAdd}>
      <Plus class="size-4" />
      إضافة فئة
    </Button>
  </div>

  <div class="flex rounded-lg border p-1 w-fit">
    <button
      class="rounded-md px-4 py-1.5 text-sm font-medium transition-colors {activeTab === 'expense'
        ? 'bg-muted text-foreground shadow-sm'
        : 'text-muted-foreground hover:text-foreground'}"
      onclick={() => (activeTab = 'expense')}
    >
      مصروفات
    </button>
    <button
      class="rounded-md px-4 py-1.5 text-sm font-medium transition-colors {activeTab === 'income'
        ? 'bg-muted text-foreground shadow-sm'
        : 'text-muted-foreground hover:text-foreground'}"
      onclick={() => (activeTab = 'income')}
    >
      إيرادات
    </button>
  </div>

  <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
    {#each displayedCategories as cat (cat.id)}
      {#if editingId === cat.id}
        <div
          class="flex flex-col gap-3 rounded-xl border-2 border-primary/30 p-3"
        >
          <div class="flex items-center gap-3">
            <div
              class="size-5 rounded-full shrink-0 ring-2 ring-offset-2"
              style="background-color: {editColor}; --tw-ring-color: {editColor}"
            ></div>
            <Input
              class="flex-1"
              bind:value={editName}
              placeholder="اسم الفئة"
            />
          </div>
          <div class="flex flex-wrap gap-1.5">
            {#each COLORS as color (color)}
              <button
                class="size-6 rounded-full transition-all hover:scale-110 {editColor === color
                  ? 'ring-2 ring-foreground ring-offset-2'
                  : ''}"
                style="background-color: {color}"
                onclick={() => (editColor = color)}
                type="button"
                aria-label="اختيار لون {color}"
              ></button>
            {/each}
          </div>
          <div class="flex gap-2">
            <Button size="sm" onclick={saveEdit}>
              <Check class="size-3" />
              حفظ
            </Button>
            <Button variant="outline" size="sm" onclick={cancelEdit}>
              <X class="size-3" />
              إلغاء
            </Button>
          </div>
        </div>
      {:else if confirmDeleteId === cat.id}
        <div class="flex items-center gap-3 rounded-xl border border-destructive/40 bg-destructive/5 p-3">
          <div
            class="size-4 rounded-full shrink-0"
            style="background-color: {cat.color}"
          ></div>
          <span class="flex-1 text-sm font-medium">{cat.name}</span>
          <p class="text-xs text-destructive font-medium whitespace-nowrap">حذف؟</p>
          <div class="flex gap-1">
            <button
              class="inline-flex items-center justify-center size-7 rounded-md bg-destructive text-destructive-foreground hover:bg-destructive/90"
              onclick={executeDelete}
              type="button"
            >
              <Check class="size-3" />
            </button>
            <button
              class="inline-flex items-center justify-center size-7 rounded-md border hover:bg-muted"
              onclick={cancelDelete}
              type="button"
            >
              <X class="size-3" />
            </button>
          </div>
        </div>
      {:else}
        <div
          class="flex items-center gap-3 rounded-xl border p-3 group transition-colors hover:bg-muted/50"
        >
          <div
            class="size-4 rounded-full shrink-0"
            style="background-color: {cat.color}"
          ></div>
          <span class="flex-1 text-sm font-medium">{cat.name}</span>
          <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
            <button
              class="inline-flex items-center justify-center size-7 rounded-md hover:bg-muted"
              onclick={() => startEdit(cat)}
              type="button"
              title="تعديل"
            >
              <Pencil class="size-3.5" />
            </button>
            <button
              class="inline-flex items-center justify-center size-7 rounded-md hover:bg-destructive/10 hover:text-destructive"
              onclick={() => confirmDelete(cat)}
              type="button"
              title="حذف"
            >
              <Trash class="size-3.5" />
            </button>
          </div>
        </div>
      {/if}
    {/each}

    {#if adding}
      <div class="flex flex-col gap-3 rounded-xl border-2 border-dashed border-muted-foreground/25 p-3">
        <Input
          bind:value={newName}
          placeholder="اسم الفئة"
        />
        <div class="flex flex-wrap gap-1.5">
            {#each COLORS as color (color)}
            <button
              class="size-6 rounded-full transition-all hover:scale-110 {newColor === color
                ? 'ring-2 ring-foreground ring-offset-2'
                : ''}"
              style="background-color: {color}"
              onclick={() => (newColor = color)}
              type="button"
              aria-label="اختيار لون {color}"
            ></button>
          {/each}
        </div>
        <div class="flex gap-2">
          <Button size="sm" onclick={saveNew}>
            <Check class="size-3" />
            حفظ
          </Button>
          <Button variant="outline" size="sm" onclick={cancelAdd}>
            <X class="size-3" />
            إلغاء
          </Button>
        </div>
      </div>
    {/if}
  </div>

  {#if displayedCategories.length === 0 && !adding}
    <div class="flex flex-col items-center justify-center gap-3 py-16 text-muted-foreground">
      <p class="text-sm">
        {activeTab === 'expense' ? 'لا توجد فئات مصروفات' : 'لا توجد فئات إيرادات'}
      </p>
      <Button variant="outline" size="sm" onclick={startAdd}>
        <Plus class="size-3" />
        إضافة فئة
      </Button>
    </div>
  {/if}
</div>
