<script lang="ts">
    import Pencil from 'lucide-svelte/icons/pencil';
    import Trash2 from 'lucide-svelte/icons/trash-2';
    import MoreVertical from 'lucide-svelte/icons/more-vertical';
    import CategoryIcon from '@/components/CategoryIcon.svelte';
    import * as DropdownMenu from '@/components/ui/dropdown-menu';
    import { formatSignedMoney, formatRelativeDate } from '@/lib/format';
    import { cn } from '@/lib/utils';
    import type { Transaction } from '@/types';

    let {
        transaction,
        style = '',
        onEdit,
        onDelete,
    }: {
        transaction: Transaction;
        style?: string;
        onEdit: (tx: Transaction) => void;
        onDelete: (tx: Transaction) => void;
    } = $props();

    const tx = $derived(transaction);
    const isIncome = $derived(tx.type === 'income');
    const title = $derived(
        tx.description?.trim() ||
            tx.category?.name ||
            (isIncome ? 'دخل' : 'مصروف'),
    );
</script>

<div
    class="group animate-fade-in-up relative flex items-center gap-3 rounded-xl px-2.5 py-2.5 transition-colors duration-200 hover:bg-muted/60"
    {style}
>
    <CategoryIcon
        icon={tx.category?.icon}
        color={tx.category?.color}
        size="md"
    />

    <div class="min-w-0 flex-1">
        <p class="truncate text-sm font-semibold text-foreground">{title}</p>
        <p class="mt-0.5 truncate text-xs text-muted-foreground">
            {#if tx.category}
                {tx.category.name}
                <span class="mx-1 opacity-40">·</span>
            {/if}
            {formatRelativeDate(tx.date)}
        </p>
    </div>

    <div class="flex shrink-0 items-center gap-1.5">
        <span
            class={cn(
                'text-sm font-semibold tabular-nums tracking-tight whitespace-nowrap',
                isIncome ? 'text-income' : 'text-expense',
            )}
        >
            {formatSignedMoney(tx.amount, tx.type)}
        </span>

        <DropdownMenu.Root>
            <DropdownMenu.Trigger
                class="flex size-8 items-center justify-center rounded-lg text-muted-foreground transition-all duration-200 hover:bg-accent hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring active:scale-95 md:opacity-0 md:group-hover:opacity-100 data-[state=open]:opacity-100 data-[state=open]:bg-accent"
                aria-label="خيارات المعاملة"
            >
                <MoreVertical class="size-4" />
            </DropdownMenu.Trigger>
            <DropdownMenu.Content align="end" class="w-40">
                <DropdownMenu.Item onSelect={() => onEdit(tx)}>
                    <Pencil class="size-4" />
                    <span>تعديل</span>
                </DropdownMenu.Item>
                <DropdownMenu.Separator />
                <DropdownMenu.Item
                    variant="destructive"
                    onSelect={() => onDelete(tx)}
                >
                    <Trash2 class="size-4" />
                    <span>حذف</span>
                </DropdownMenu.Item>
            </DropdownMenu.Content>
        </DropdownMenu.Root>
    </div>
</div>
