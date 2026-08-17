<script lang="ts">
    import Monitor from 'lucide-svelte/icons/monitor';
    import Moon from 'lucide-svelte/icons/moon';
    import Sun from 'lucide-svelte/icons/sun';
    import type { Component, SvelteComponent } from 'svelte';
    import { themeState } from '@/lib/theme.svelte';
    import type { Appearance } from '@/types';

    const { appearance, updateAppearance } = themeState();

    type IconComponent =
        | Component<{ class?: string }>
        | (new (...args: any[]) => SvelteComponent<{ class?: string }>);

    const tabs: { value: Appearance; Icon: IconComponent; label: string }[] = [
        { value: 'light', Icon: Sun, label: 'فاتح' },
        { value: 'dark', Icon: Moon, label: 'داكن' },
        { value: 'system', Icon: Monitor, label: 'النظام' },
    ];

    function handleAppearanceChange(value: Appearance) {
        updateAppearance(value);
    }
</script>

<div class="space-y-6">
    <div
        class="grid grid-cols-3 gap-1 rounded-xl border border-border bg-muted/60 p-1"
        role="radiogroup"
        aria-label="مظهر التطبيق"
    >
        {#each tabs as { value, Icon, label } (value)}
            {@const active = appearance.value === value}
            <button
                type="button"
                role="radio"
                aria-checked={active}
                onclick={() => handleAppearanceChange(value)}
                class="flex items-center justify-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition-all active:scale-95 {active
                    ? 'bg-card text-foreground shadow-soft'
                    : 'text-muted-foreground hover:text-foreground'}"
            >
                <Icon
                    class="size-4 shrink-0 {active ? 'text-primary' : ''}"
                />
                <span>{label}</span>
            </button>
        {/each}
    </div>

    <!-- Live preview -->
    <div
        class="overflow-hidden rounded-xl border border-border bg-background shadow-soft"
    >
        <div
            class="flex items-center gap-2 border-b border-border bg-muted/40 px-4 py-2.5"
        >
            <span class="size-2.5 rounded-full bg-destructive/60"></span>
            <span class="size-2.5 rounded-full bg-primary/40"></span>
            <span class="size-2.5 rounded-full bg-muted-foreground/40"></span>
            <span class="ms-2 text-xs text-muted-foreground">معاينة</span>
        </div>
        <div class="space-y-3 p-4">
            <div class="flex items-center gap-3">
                <div
                    class="flex size-9 items-center justify-center rounded-full bg-primary text-primary-foreground"
                >
                    <span class="text-xs font-semibold">م</span>
                </div>
                <div class="space-y-1.5">
                    <div class="h-2.5 w-24 rounded-full bg-foreground/80"></div>
                    <div class="h-2 w-16 rounded-full bg-muted-foreground/40"></div>
                </div>
            </div>
            <div class="rounded-lg border border-border bg-card p-3">
                <div class="mb-2 flex items-center justify-between">
                    <div class="h-2 w-20 rounded-full bg-muted-foreground/40"></div>
                    <div class="h-2 w-10 rounded-full bg-primary/60"></div>
                </div>
                <div class="h-2 w-full rounded-full bg-muted"></div>
            </div>
        </div>
    </div>
</div>
