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
  import Plus from 'lucide-svelte/icons/plus';
  import Shapes from 'lucide-svelte/icons/shapes';
  import Trash2 from 'lucide-svelte/icons/trash-2';
  import TriangleAlert from 'lucide-svelte/icons/triangle-alert';
  import { onMount } from 'svelte';
  import AppHead from '@/components/AppHead.svelte';
  import CategoryCard from '@/components/categories/CategoryCard.svelte';
  import CategoryFormDialog from '@/components/categories/CategoryFormDialog.svelte';
  import { COLOR_OPTIONS, ICON_OPTIONS } from '@/components/categories/constants';
  import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
  } from '@/components/ui/alert-dialog';
  import { Button } from '@/components/ui/button';
  import {
    Empty,
    EmptyContent,
    EmptyDescription,
    EmptyHeader,
    EmptyMedia,
    EmptyTitle,
  } from '@/components/ui/empty';
  import { Skeleton } from '@/components/ui/skeleton';
  import { Spinner } from '@/components/ui/spinner';
  import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
  import { store, update, destroy } from '@/routes/categories';
  import type { Category, TransactionType } from '@/types';

  const { props } = usePage<{
    expenseCategories: Category[];
    incomeCategories: Category[];
  }>();

  let expenseCategories = $derived(props.expenseCategories);
  let incomeCategories = $derived(props.incomeCategories);

  let activeTab = $state<TransactionType>('expense');

  // Brief shimmer on first client paint (client-rendered SPA, no SSR).
  let ready = $state(false);
  onMount(() => {
    ready = true;
  });

  // --- Add / Edit dialog ---------------------------------------------------
  let formOpen = $state(false);
  let formMode = $state<'add' | 'edit'>('add');
  let editingId = $state<number | null>(null);

  const form = useForm({
    name: '',
    type: 'expense' as TransactionType,
    icon: ICON_OPTIONS[0] as string,
    color: COLOR_OPTIONS[0] as string,
  });

  function openAdd() {
    formMode = 'add';
    editingId = null;
    form.clearErrors();
    form.reset();
    form.type = activeTab;
    form.icon = ICON_OPTIONS[0];
    form.color = COLOR_OPTIONS[0];
    formOpen = true;
  }

  function openEdit(cat: Category) {
    formMode = 'edit';
    editingId = cat.id;
    form.clearErrors();
    form.name = cat.name;
    form.type = cat.type;
    form.icon = cat.icon ?? ICON_OPTIONS[0];
    form.color = cat.color ?? COLOR_OPTIONS[0];
    formOpen = true;
  }

  function submitForm() {
    if (formMode === 'add') {
      form.post(store.url(), {
        preserveScroll: true,
        onSuccess: () => {
          formOpen = false;
          form.reset();
          form.color = COLOR_OPTIONS[0];
          form.icon = ICON_OPTIONS[0];
        },
      });
    } else if (editingId !== null) {
      form.put(update.url(editingId), {
        preserveScroll: true,
        onSuccess: () => {
          formOpen = false;
        },
      });
    }
  }

  // --- Delete confirmation -------------------------------------------------
  let deleteOpen = $state(false);
  let deleteTarget = $state<Category | null>(null);
  let deleting = $state(false);

  function requestDelete(cat: Category) {
    deleteTarget = cat;
    deleteOpen = true;
  }

  function executeDelete() {
    if (deleteTarget === null) {
      return;
    }

    router.delete(destroy.url(deleteTarget.id), {
      preserveScroll: true,
      onStart: () => (deleting = true),
      onFinish: () => (deleting = false),
      onSuccess: () => {
        deleteOpen = false;
        deleteTarget = null;
      },
    });
  }

  const saving = $derived(form.processing);
</script>

<AppHead title="الفئات" />

{#snippet grid(cats: Category[])}
  {#if !ready}
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
      {#each Array(6) as _, i (i)}
        <div class="flex items-center gap-3 rounded-2xl border border-border bg-card p-3.5 md:p-4">
          <Skeleton class="size-12 rounded-2xl" />
          <div class="flex flex-1 flex-col gap-2">
            <Skeleton class="h-3.5 w-2/3 rounded" />
            <Skeleton class="h-2.5 w-1/3 rounded" />
          </div>
        </div>
      {/each}
    </div>
  {:else if cats.length === 0}
    <Empty class="rounded-2xl border border-dashed border-border bg-card/40 py-14">
      <EmptyHeader>
        <EmptyMedia variant="icon" class="size-12 rounded-2xl bg-muted text-muted-foreground">
          <Shapes class="size-6" />
        </EmptyMedia>
        <EmptyTitle>
          {activeTab === 'expense' ? 'لا توجد فئات مصروفات' : 'لا توجد فئات إيرادات'}
        </EmptyTitle>
        <EmptyDescription>
          أنشئ أول فئة لتنظيم {activeTab === 'expense' ? 'مصروفاتك' : 'إيراداتك'} بسهولة.
        </EmptyDescription>
      </EmptyHeader>
      <EmptyContent>
        <Button onclick={openAdd}>
          <Plus class="size-4" />
          إضافة فئة
        </Button>
      </EmptyContent>
    </Empty>
  {:else}
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
      {#each cats as cat, i (cat.id)}
        <CategoryCard
          category={cat}
          index={i}
          onedit={openEdit}
          ondelete={requestDelete}
        />
      {/each}
    </div>
  {/if}
{/snippet}

<div class="flex flex-1 flex-col gap-6 px-4 py-6 md:px-6 md:py-8">
  <!-- Header -->
  <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div class="space-y-1">
      <h1 class="text-2xl font-semibold tracking-tight text-foreground">الفئات</h1>
      <p class="text-sm text-muted-foreground">
        نظّم فئات مصروفاتك وإيراداتك في مكان واحد.
      </p>
    </div>
    <Button size="lg" class="w-full sm:w-auto" onclick={openAdd}>
      <Plus class="size-4" />
      إضافة فئة
    </Button>
  </div>

  <!-- Tabs segmented control + content -->
  <Tabs
    value={activeTab}
    onValueChange={(value) => (activeTab = value as TransactionType)}
    class="w-full gap-5"
  >
    <TabsList class="grid h-10 w-full grid-cols-2 rounded-xl sm:w-72">
      <TabsTrigger value="expense" class="gap-2 rounded-lg">
        مصروفات
        <span
          class="min-w-5 rounded-full bg-expense-muted px-1.5 py-0.5 text-[11px] font-semibold leading-none text-expense"
        >
          {expenseCategories.length}
        </span>
      </TabsTrigger>
      <TabsTrigger value="income" class="gap-2 rounded-lg">
        إيرادات
        <span
          class="min-w-5 rounded-full bg-income-muted px-1.5 py-0.5 text-[11px] font-semibold leading-none text-income"
        >
          {incomeCategories.length}
        </span>
      </TabsTrigger>
    </TabsList>

    <TabsContent value="expense" class="mt-0 focus-visible:outline-none">
      {@render grid(expenseCategories)}
    </TabsContent>
    <TabsContent value="income" class="mt-0 focus-visible:outline-none">
      {@render grid(incomeCategories)}
    </TabsContent>
  </Tabs>
</div>

<!-- Add / Edit dialog (Dialog on desktop, Drawer on mobile) -->
<CategoryFormDialog
  bind:open={formOpen}
  mode={formMode}
  {form}
  {saving}
  onsubmit={submitForm}
/>

<!-- Delete confirmation -->
<AlertDialog bind:open={deleteOpen}>
  <AlertDialogContent>
    <AlertDialogHeader>
      <div
        class="mx-auto mb-1 flex size-11 items-center justify-center rounded-full bg-destructive/10 text-destructive sm:mx-0"
      >
        <TriangleAlert class="size-5" />
      </div>
      <AlertDialogTitle>حذف الفئة</AlertDialogTitle>
      <AlertDialogDescription>
        هل أنت متأكد من حذف فئة
        <span class="font-semibold text-foreground">«{deleteTarget?.name}»</span>؟
        لا يمكن التراجع عن هذا الإجراء.
      </AlertDialogDescription>
    </AlertDialogHeader>
    <AlertDialogFooter>
      <AlertDialogCancel disabled={deleting}>إلغاء</AlertDialogCancel>
      <AlertDialogAction
        variant="destructive"
        disabled={deleting}
        onclick={(e: MouseEvent) => {
          e.preventDefault();
          executeDelete();
        }}
      >
        {#if deleting}
          <Spinner class="size-4" />
        {:else}
          <Trash2 class="size-4" />
        {/if}
        حذف
      </AlertDialogAction>
    </AlertDialogFooter>
  </AlertDialogContent>
</AlertDialog>
