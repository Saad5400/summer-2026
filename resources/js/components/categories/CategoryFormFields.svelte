<script lang="ts">
    import Check from 'lucide-svelte/icons/check';
    import CategoryIcon from '@/components/CategoryIcon.svelte';
    import InputError from '@/components/InputError.svelte';
    import { Field, FieldLabel } from '@/components/ui/field';
    import { Input } from '@/components/ui/input';
    import type { TransactionType } from '@/types';
    import { COLOR_OPTIONS, ICON_OPTIONS } from './constants';

    // Inertia useForm() proxy — accessed for name/type/icon/color/errors.
    let {
        form,
        onsubmit,
    }: {
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        form: any;
        onsubmit?: () => void;
    } = $props();

    function setType(type: TransactionType) {
        form.type = type;
    }

    function handleKeydown(event: KeyboardEvent) {
        if (event.key === 'Enter') {
            event.preventDefault();
            onsubmit?.();
        }
    }
</script>

<div class="flex flex-col gap-5">
    <!-- Live preview -->
    <div
        class="flex flex-col items-center gap-2.5 rounded-2xl border border-border bg-muted/40 px-4 py-5"
    >
        <CategoryIcon
            icon={form.icon}
            color={form.color}
            size="lg"
            class="size-14 rounded-2xl [&_svg]:size-7"
        />
        <span class="max-w-full truncate text-sm font-semibold text-foreground">
            {form.name?.trim() ? form.name : 'اسم الفئة'}
        </span>
    </div>

    <!-- Name -->
    <Field>
        <FieldLabel for="category-name">اسم الفئة</FieldLabel>
        <Input
            id="category-name"
            bind:value={form.name}
            placeholder="مثال: مطاعم، رواتب…"
            autocomplete="off"
            onkeydown={handleKeydown}
        />
        <InputError message={form.errors.name} />
    </Field>

    <!-- Type toggle -->
    <Field>
        <FieldLabel>النوع</FieldLabel>
        <div
            class="grid grid-cols-2 gap-1.5 rounded-xl border border-border bg-muted/50 p-1"
        >
            <button
                type="button"
                onclick={() => setType('expense')}
                class="flex items-center justify-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium transition-all active:scale-95 {form.type ===
                'expense'
                    ? 'bg-expense-muted text-expense shadow-sm'
                    : 'text-muted-foreground hover:text-foreground'}"
            >
                مصروفات
            </button>
            <button
                type="button"
                onclick={() => setType('income')}
                class="flex items-center justify-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium transition-all active:scale-95 {form.type ===
                'income'
                    ? 'bg-income-muted text-income shadow-sm'
                    : 'text-muted-foreground hover:text-foreground'}"
            >
                إيرادات
            </button>
        </div>
        <InputError message={form.errors.type} />
    </Field>

    <!-- Icon picker -->
    <Field>
        <FieldLabel>الأيقونة</FieldLabel>
        <div class="grid grid-cols-6 gap-2 sm:grid-cols-7">
            {#each ICON_OPTIONS as option (option)}
                <button
                    type="button"
                    onclick={() => (form.icon = option)}
                    aria-label="اختيار أيقونة {option}"
                    aria-pressed={form.icon === option}
                    class="flex aspect-square items-center justify-center rounded-xl border transition-all active:scale-95 {form.icon ===
                    option
                        ? 'border-primary bg-accent ring-2 ring-primary'
                        : 'border-border bg-card hover:bg-accent'}"
                >
                    <CategoryIcon
                        icon={option}
                        color={form.color}
                        size="sm"
                        class="bg-transparent"
                    />
                </button>
            {/each}
        </div>
        <InputError message={form.errors.icon} />
    </Field>

    <!-- Color picker -->
    <Field>
        <FieldLabel>اللون</FieldLabel>
        <div class="flex flex-wrap gap-2.5">
            {#each COLOR_OPTIONS as color (color)}
                <button
                    type="button"
                    onclick={() => (form.color = color)}
                    aria-label="اختيار لون {color}"
                    aria-pressed={form.color === color}
                    class="flex size-9 items-center justify-center rounded-full text-white transition-all active:scale-90 {form.color ===
                    color
                        ? 'ring-2 ring-offset-2 ring-offset-background'
                        : 'hover:scale-110'}"
                    style="background-color: {color}; {form.color === color
                        ? `--tw-ring-color: ${color};`
                        : ''}"
                >
                    {#if form.color === color}
                        <Check class="size-4" />
                    {/if}
                </button>
            {/each}
        </div>
        <InputError message={form.errors.color} />
    </Field>
</div>
