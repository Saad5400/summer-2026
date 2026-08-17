<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import ChartColumn from 'lucide-svelte/icons/chart-column';
    import ScanLine from 'lucide-svelte/icons/scan-line';
    import ShieldCheck from 'lucide-svelte/icons/shield-check';
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

    const features = [
        {
            icon: ScanLine,
            title: 'تتبّع تلقائي',
            text: 'سجّل مصاريفك ودخلك بلمسة واحدة وبدون عناء.',
        },
        {
            icon: ChartColumn,
            title: 'رؤى واضحة',
            text: 'تقارير أنيقة تكشف أين يذهب كل ريال.',
        },
        {
            icon: ShieldCheck,
            title: 'أمان وخصوصية',
            text: 'بياناتك مشفّرة ومحمية في كل وقت.',
        },
    ];
</script>

<div class="relative min-h-svh lg:grid lg:grid-cols-2">
    <!-- Branded panel (desktop only) -->
    <div
        class="relative hidden overflow-hidden bg-primary text-primary-foreground lg:flex lg:flex-col lg:justify-between lg:p-12"
    >
        <!-- depth + glow overlays -->
        <div
            aria-hidden="true"
            class="pointer-events-none absolute inset-0 [background:radial-gradient(120%_80%_at_100%_0%,color-mix(in_oklch,var(--primary-foreground)_16%,transparent),transparent_60%)]"
        ></div>
        <!-- abstract ascending-bars motif echoing the mark -->
        <div
            aria-hidden="true"
            class="pointer-events-none absolute bottom-[-2rem] end-[-2rem] flex items-end gap-3 opacity-[0.12]"
        >
            <div class="h-24 w-12 rounded-2xl bg-primary-foreground"></div>
            <div class="h-40 w-12 rounded-2xl bg-primary-foreground"></div>
            <div class="h-64 w-12 rounded-2xl bg-primary-foreground"></div>
        </div>

        <!-- Brand -->
        <Link
            href={home()}
            class="relative z-10 flex items-center gap-3 transition-opacity hover:opacity-90"
        >
            <div
                class="flex size-11 items-center justify-center rounded-xl bg-primary-foreground/15 ring-1 ring-primary-foreground/20"
            >
                <AppLogoIcon class="size-6" />
            </div>
            <span class="text-xl font-semibold tracking-tight">ميزان</span>
        </Link>

        <!-- Value proposition -->
        <div class="relative z-10 max-w-md">
            <h2 class="text-3xl font-bold leading-snug tracking-tight text-balance">
                تتبّع مصاريفك بذكاء
            </h2>
            <p class="mt-3 text-base leading-relaxed text-primary-foreground/80">
                ميزان يمنحك صورة كاملة عن أموالك، ويحوّل أرقامك اليومية إلى قرارات
                واثقة.
            </p>

            <ul class="mt-9 space-y-5">
                {#each features as feature (feature.title)}
                    <li class="flex items-start gap-4">
                        <div
                            class="mt-0.5 flex size-10 shrink-0 items-center justify-center rounded-xl bg-primary-foreground/15 ring-1 ring-primary-foreground/20"
                        >
                            <feature.icon class="size-5" />
                        </div>
                        <div class="space-y-0.5">
                            <p class="font-semibold">{feature.title}</p>
                            <p class="text-sm text-primary-foreground/75">
                                {feature.text}
                            </p>
                        </div>
                    </li>
                {/each}
            </ul>
        </div>

        <!-- Footer -->
        <p class="relative z-10 text-sm text-primary-foreground/70">
            مالك بين يديك، بثقة ووضوح.
        </p>
    </div>

    <!-- Form column -->
    <div
        class="relative flex min-h-svh flex-col items-center justify-center overflow-hidden bg-background px-4 py-10 sm:px-6"
    >
        <div
            aria-hidden="true"
            class="pointer-events-none absolute inset-0 -z-10 lg:hidden"
        >
            <div
                class="absolute inset-0 opacity-60 [background-image:linear-gradient(to_right,var(--border)_1px,transparent_1px),linear-gradient(to_bottom,var(--border)_1px,transparent_1px)] [background-size:34px_34px] [mask-image:radial-gradient(ellipse_65%_55%_at_50%_0%,#000_35%,transparent_100%)]"
            ></div>
            <div
                class="absolute left-1/2 top-[-7rem] h-[26rem] w-[34rem] -translate-x-1/2 rounded-full bg-primary/20 blur-[120px] dark:bg-primary/25"
            ></div>
        </div>

        <div class="w-full max-w-sm">
            <!-- Brand (mobile only — desktop shows it in the panel) -->
            <div
                class="animate-fade-in mb-7 flex flex-col items-center gap-3 lg:hidden"
            >
                <Link
                    href={home()}
                    class="flex flex-col items-center gap-3 transition-opacity hover:opacity-90"
                >
                    <div
                        class="flex size-12 items-center justify-center rounded-xl bg-primary text-primary-foreground shadow-soft"
                    >
                        <AppLogoIcon class="size-7" />
                    </div>
                    <span
                        class="text-lg font-semibold tracking-tight text-foreground"
                        >ميزان</span
                    >
                </Link>
            </div>

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
</div>
