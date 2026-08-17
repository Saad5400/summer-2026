<script lang="ts">
    import { Button } from '@/components/ui/button';
    import {
        Dialog,
        DialogContent,
        DialogDescription,
        DialogHeader,
        DialogTitle,
    } from '@/components/ui/dialog';
    import {
        Drawer,
        DrawerContent,
        DrawerDescription,
        DrawerFooter,
        DrawerHeader,
        DrawerTitle,
    } from '@/components/ui/drawer';
    import { Spinner } from '@/components/ui/spinner';
    import { IsMobile } from '@/hooks/is-mobile.svelte.js';
    import CategoryFormFields from './CategoryFormFields.svelte';

    let {
        open = $bindable(false),
        mode = 'add',
        form,
        saving = false,
        onsubmit,
    }: {
        open?: boolean;
        mode?: 'add' | 'edit';
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        form: any;
        saving?: boolean;
        onsubmit: () => void;
    } = $props();

    const isMobile = new IsMobile();

    const title = $derived(mode === 'add' ? 'إضافة فئة جديدة' : 'تعديل الفئة');
    const description = $derived(
        mode === 'add'
            ? 'أنشئ فئة مخصصة لتنظيم معاملاتك.'
            : 'حدّث اسم الفئة أو أيقونتها أو لونها.',
    );
    const submitLabel = $derived(
        mode === 'add' ? 'إضافة الفئة' : 'حفظ التغييرات',
    );
</script>

{#snippet footer()}
    <Button
        variant="outline"
        class="w-full sm:w-auto"
        onclick={() => (open = false)}
        disabled={saving}
    >
        إلغاء
    </Button>
    <Button class="w-full sm:w-auto" onclick={onsubmit} disabled={saving}>
        {#if saving}
            <Spinner class="size-4" />
        {/if}
        {submitLabel}
    </Button>
{/snippet}

{#if isMobile.current}
    <Drawer bind:open>
        <DrawerContent class="max-h-[92vh]">
            <DrawerHeader class="text-start">
                <DrawerTitle>{title}</DrawerTitle>
                <DrawerDescription>{description}</DrawerDescription>
            </DrawerHeader>
            <div class="overflow-y-auto px-4 pb-2">
                <CategoryFormFields {form} {onsubmit} />
            </div>
            <DrawerFooter class="flex-col-reverse">
                {@render footer()}
            </DrawerFooter>
        </DrawerContent>
    </Drawer>
{:else}
    <Dialog bind:open>
        <DialogContent
            class="max-h-[90vh] gap-0 overflow-hidden p-0 sm:max-w-md"
        >
            <DialogHeader class="border-b border-border p-5 text-start">
                <DialogTitle>{title}</DialogTitle>
                <DialogDescription>{description}</DialogDescription>
            </DialogHeader>
            <div class="overflow-y-auto p-5">
                <CategoryFormFields {form} {onsubmit} />
            </div>
            <div
                class="flex flex-col-reverse gap-2 border-t border-border bg-muted/40 p-4 sm:flex-row sm:justify-end"
            >
                {@render footer()}
            </div>
        </DialogContent>
    </Dialog>
{/if}
