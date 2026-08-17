<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import type { Snippet } from 'svelte';
    import AppLogoIcon from '@/components/AppLogoIcon.svelte';
    import { Card } from '@/components/ui/card';
    import { home } from '@/routes';

    let {
        title = '',
        description = '',
        children,
    }: {
        title?: string;
        description?: string;
        children?: Snippet;
    } = $props();
</script>

<div
    class="relative flex min-h-svh flex-col items-center justify-center overflow-hidden bg-background px-4 py-10 sm:px-6"
>
    <!-- Decorative backdrop: soft primary glow + faint grid -->
    <div aria-hidden="true" class="pointer-events-none absolute inset-0 -z-10">
        <div
            class="absolute inset-0 opacity-60 [background-image:linear-gradient(to_right,var(--border)_1px,transparent_1px),linear-gradient(to_bottom,var(--border)_1px,transparent_1px)] [background-size:34px_34px] [mask-image:radial-gradient(ellipse_65%_55%_at_50%_0%,#000_35%,transparent_100%)]"
        ></div>
        <div
            class="absolute left-1/2 top-[-7rem] h-[26rem] w-[34rem] -translate-x-1/2 rounded-full bg-primary/20 blur-[120px] dark:bg-primary/25"
        ></div>
        <div
            class="absolute bottom-[-9rem] end-[-6rem] h-[22rem] w-[22rem] rounded-full bg-primary/10 blur-[100px]"
        ></div>
    </div>

    <div class="w-full max-w-sm">
        <!-- Brand -->
        <div class="animate-fade-in mb-7 flex flex-col items-center gap-3">
            <Link
                href={home()}
                class="flex flex-col items-center gap-3 transition-opacity hover:opacity-90"
            >
                <div
                    class="flex size-12 items-center justify-center rounded-xl bg-primary text-primary-foreground shadow-soft"
                >
                    <AppLogoIcon class="size-7" />
                </div>
                <span class="text-lg font-semibold tracking-tight text-foreground"
                    >ميزان</span
                >
            </Link>
        </div>

        <!-- Card -->
        <Card
            class="animate-fade-in-up gap-0 rounded-2xl border border-border bg-card py-0 shadow-soft ring-0"
        >
            <div class="p-6 sm:p-8">
                <div class="mb-6 space-y-2 text-center">
                    {#if title}
                        <h1
                            class="text-xl font-semibold tracking-tight text-foreground"
                        >
                            {title}
                        </h1>
                    {/if}
                    {#if description}
                        <p
                            class="text-pretty text-sm leading-relaxed text-muted-foreground"
                        >
                            {description}
                        </p>
                    {/if}
                </div>
                {@render children?.()}
            </div>
        </Card>
    </div>
</div>
