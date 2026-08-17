<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import Breadcrumbs from '@/components/Breadcrumbs.svelte';
    import { SidebarTrigger } from '@/components/ui/sidebar';
    import { toUrl } from '@/lib/utils';
    import { dashboard } from '@/routes';
    import type { BreadcrumbItem } from '@/types';

    let {
        breadcrumbs = [],
    }: {
        breadcrumbs?: BreadcrumbItem[];
    } = $props();
</script>

<header
    class="sticky top-0 z-30 flex h-16 shrink-0 items-center gap-2 border-b border-border/60 bg-background/70 px-4 backdrop-blur-xl transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-14 md:px-6"
>
    <div class="flex items-center gap-2">
        <SidebarTrigger class="-ms-1 hidden md:flex" />
        {#if breadcrumbs && breadcrumbs.length > 0}
            <div class="hidden md:block">
                <Breadcrumbs {breadcrumbs} />
            </div>
        {/if}
    </div>

    <!-- Mobile brand (sidebar is hidden on mobile) -->
    <Link
        href={toUrl(dashboard())}
        class="flex items-center gap-2 md:hidden"
        aria-label="ميزان"
    >
        <span
            class="flex size-8 items-center justify-center rounded-lg bg-primary text-primary-foreground"
        >
            <svg viewBox="0 0 32 32" fill="none" class="size-[18px]">
                <rect x="6" y="17" width="4.5" height="9" rx="2.25" fill="currentColor" opacity="0.55" />
                <rect x="13.75" y="11" width="4.5" height="15" rx="2.25" fill="currentColor" opacity="0.8" />
                <rect x="21.5" y="6" width="4.5" height="20" rx="2.25" fill="currentColor" />
            </svg>
        </span>
        <span class="text-lg font-bold tracking-tight">ميزان</span>
    </Link>
</header>
