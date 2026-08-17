<script lang="ts">
  import EllipsisVertical from 'lucide-svelte/icons/ellipsis-vertical';
  import Lock from 'lucide-svelte/icons/lock';
  import Pencil from 'lucide-svelte/icons/pencil';
  import Trash2 from 'lucide-svelte/icons/trash-2';
  import CategoryIcon from '@/components/CategoryIcon.svelte';
  import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
  } from '@/components/ui/dropdown-menu';
  import type { Category } from '@/types';

  let {
    category,
    index = 0,
    onedit,
    ondelete,
  }: {
    category: Category;
    index?: number;
    onedit: (cat: Category) => void;
    ondelete: (cat: Category) => void;
  } = $props();

  const isSystem = $derived(category.user_id == null);
</script>

<div
  class="group relative flex animate-fade-in-up items-center gap-3 rounded-2xl border border-border bg-card p-3.5 shadow-soft transition-all duration-200 hover:-translate-y-0.5 hover:border-border hover:shadow-elevated md:p-4"
  style="animation-delay: {Math.min(index, 12) * 40}ms;"
>
  <CategoryIcon icon={category.icon} color={category.color} size="lg" />

  <div class="flex min-w-0 flex-1 flex-col">
    <span class="truncate text-sm font-semibold text-foreground md:text-base">
      {category.name}
    </span>
    {#if isSystem}
      <span class="mt-0.5 inline-flex items-center gap-1 text-[11px] text-muted-foreground">
        <Lock class="size-3" />
        فئة افتراضية
      </span>
    {/if}
  </div>

  {#if isSystem}
    <span
      class="size-2.5 shrink-0 rounded-full ring-2 ring-background"
      style="background-color: {category.color ?? 'var(--primary)'};"
      aria-hidden="true"
    ></span>
  {:else}
    <DropdownMenu>
      <DropdownMenuTrigger
        class="inline-flex size-8 shrink-0 items-center justify-center rounded-lg text-muted-foreground opacity-100 transition-all hover:bg-accent hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring active:scale-95 md:opacity-0 md:group-hover:opacity-100 md:data-[state=open]:opacity-100"
        aria-label="خيارات الفئة {category.name}"
      >
        <EllipsisVertical class="size-4.5" />
      </DropdownMenuTrigger>
      <DropdownMenuContent align="end" class="w-40">
        <DropdownMenuItem onSelect={() => onedit(category)}>
          <Pencil class="size-4" />
          تعديل
        </DropdownMenuItem>
        <DropdownMenuSeparator />
        <DropdownMenuItem variant="destructive" onSelect={() => ondelete(category)}>
          <Trash2 class="size-4" />
          حذف
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>
  {/if}
</div>
