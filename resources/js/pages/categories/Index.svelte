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
  import { usePage, router, useForm } from '@inertiajs/svelte';
  import Check from 'lucide-svelte/icons/check';
  import Pencil from 'lucide-svelte/icons/pencil';
  import Plus from 'lucide-svelte/icons/plus';
  import Trash from 'lucide-svelte/icons/trash';
  import X from 'lucide-svelte/icons/x';
  import AppHead from '@/components/AppHead.svelte';
  import Heading from '@/components/Heading.svelte';
  import { Button } from '@/components/ui/button';
  import { Input } from '@/components/ui/input';
  import { Spinner } from '@/components/ui/spinner';
  import { store, update, destroy } from '@/routes/categories';
  import type { Category, TransactionType } from '@/types';

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

  const { props } = usePage<{
    expenseCategories: Category[];
    incomeCategories: Category[];
  }>();

  let expenseCategories = $derived(props.expenseCategories);
  let incomeCategories = $derived(props.incomeCategories);

  let activeTab = $state<TransactionType>('expense');
  let adding = $state(false);
  let editingId = $state<number | null>(null);
  let confirmDeleteId = $state<number | null>(null);

  const addForm = useForm({ name: '', type: 'expense' as TransactionType, icon: '', color: COLORS[0] });
  const editForm = useForm({ name: '', icon: '', color: '', type: '' });

  const displayedCategories = $derived(
    activeTab === 'expense' ? expenseCategories : incomeCategories,
  );

  function handleTabChange(tab: TransactionType) {
    activeTab = tab;
    addForm.type = activeTab;
    addForm.clearErrors();
  }

  function startAdd() {
    adding = true;
    editingId = null;
    confirmDeleteId = null;
    addForm.reset();
    addForm.color = COLORS[0];
    addForm.type = activeTab;
  }

  function cancelAdd() {
    adding = false;
    addForm.reset();
    addForm.color = COLORS[0];
  }

  function saveNew() {
    addForm.type = activeTab;
    addForm.post(store.url(), {
      preserveScroll: true,
      onSuccess: () => {
        adding = false;
        addForm.reset();
        addForm.color = COLORS[0];
      },
    });
  }

  function startEdit(cat: Category) {
    editingId = cat.id;
    editForm.name = cat.name;
    editForm.icon = cat.icon;
    editForm.color = cat.color;
    editForm.type = cat.type;
    adding = false;
    confirmDeleteId = null;
  }

  function cancelEdit() {
    editingId = null;
    editForm.reset();
    editForm.color = COLORS[0];
  }

  function saveEdit() {
    if (editingId === null) {
return;
}

    editForm.put(update.url(editingId), {
      preserveScroll: true,
      onSuccess: () => {
        editingId = null;
      },
    });
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

    router.delete(destroy.url(confirmDeleteId), {
      preserveScroll: true,
      onSuccess: () => {
        confirmDeleteId = null;
      },
    });
  }

  function isSystemCategory(cat: Category): boolean {
    return cat.user_id == null;
  }

  let saving = $derived(addForm.processing || editForm.processing);
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
      onclick={() => handleTabChange('expense')}
    >
      مصروفات
    </button>
    <button
      class="rounded-md px-4 py-1.5 text-sm font-medium transition-colors {activeTab === 'income'
        ? 'bg-muted text-foreground shadow-sm'
        : 'text-muted-foreground hover:text-foreground'}"
      onclick={() => handleTabChange('income')}
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
              class="size-5 shrink-0 rounded-full ring-2 ring-offset-2"
              style="background-color: {editForm.color}; --tw-ring-color: {editForm.color}"
            ></div>
            <Input
              class="flex-1"
              bind:value={editForm.name}
              placeholder="اسم الفئة"
            />
          </div>
          {#if editForm.errors.name}
            <p class="text-xs text-destructive">{editForm.errors.name}</p>
          {/if}
          <div class="flex flex-wrap gap-1.5">
            {#each COLORS as color (color)}
              <button
                class="size-10 md:size-6 rounded-full transition-all hover:scale-110 {editForm.color === color
                  ? 'ring-2 ring-foreground ring-offset-2'
                  : ''}"
                style="background-color: {color}"
                onclick={() => (editForm.color = color)}
                type="button"
                aria-label="اختيار لون {color}"
              ></button>
            {/each}
          </div>
          <div class="flex gap-2">
            <Button size="sm" onclick={saveEdit} disabled={saving}>
              {#if saving}
                <Spinner class="size-3" />
              {:else}
                <Check class="size-3" />
              {/if}
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
            class="size-4 shrink-0 rounded-full"
            style="background-color: {cat.color}"
          ></div>
          <span class="flex-1 text-sm font-medium">{cat.name}</span>
          <p class="whitespace-nowrap text-xs font-medium text-destructive">حذف؟</p>
          <div class="flex gap-1">
            <button
              class="inline-flex size-7 items-center justify-center rounded-md bg-destructive text-destructive-foreground hover:bg-destructive/90"
              onclick={executeDelete}
              type="button"
            >
              <Check class="size-3" />
            </button>
            <button
              class="inline-flex size-7 items-center justify-center rounded-md border hover:bg-muted"
              onclick={cancelDelete}
              type="button"
            >
              <X class="size-3" />
            </button>
          </div>
        </div>
      {:else}
        <div class="group flex items-center gap-3 rounded-xl border p-3">
          <div
            class="size-4 shrink-0 rounded-full"
            style="background-color: {cat.color}"
          ></div>
          <span class="flex-1 text-sm font-medium">{cat.name}</span>
          {#if isSystemCategory(cat)}
            <span class="text-[10px] text-muted-foreground/60 px-1">افتراضي</span>
          {:else}
            <button
              class="flex items-center gap-1 rounded-md px-2.5 py-1.5 text-xs font-medium text-muted-foreground hover:bg-muted hover:text-foreground transition-colors"
              onclick={() => startEdit(cat)}
              type="button"
            >
              <Pencil class="size-3" />
              تعديل
            </button>
            <button
              class="flex items-center gap-1 rounded-md px-2.5 py-1.5 text-xs font-medium text-muted-foreground hover:bg-destructive/10 hover:text-destructive transition-colors"
              onclick={() => confirmDelete(cat)}
              type="button"
            >
              <Trash class="size-3" />
              حذف
            </button>
          {/if}
        </div>
      {/if}
    {/each}

    {#if adding}
      <div class="flex flex-col gap-3 rounded-xl border-2 border-dashed border-muted-foreground/25 p-3">
        <Input
          bind:value={addForm.name}
          placeholder="اسم الفئة"
        />
        {#if addForm.errors.name}
          <p class="text-xs text-destructive">{addForm.errors.name}</p>
        {/if}
        <div class="flex flex-wrap gap-1.5">
          {#each COLORS as color (color)}
            <button
              class="size-10 md:size-6 rounded-full transition-all hover:scale-110 {addForm.color === color
                ? 'ring-2 ring-foreground ring-offset-2'
                : ''}"
              style="background-color: {color}"
              onclick={() => (addForm.color = color)}
              type="button"
              aria-label="اختيار لون {color}"
            ></button>
          {/each}
        </div>
        <div class="flex gap-2">
          <Button size="sm" onclick={saveNew} disabled={saving}>
            {#if saving}
              <Spinner class="size-3" />
            {:else}
              <Check class="size-3" />
            {/if}
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
