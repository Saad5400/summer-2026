<script lang="ts">
    import type { Snippet } from 'svelte';
    import AppTopbar from '@/components/AppTopbar.svelte';
    import MobileBottomNav from '@/components/MobileBottomNav.svelte';
    import { Toaster } from '@/components/ui/sonner';
    import type { BreadcrumbItem } from '@/types';

    let {
        breadcrumbs = [],
        children,
    }: {
        breadcrumbs?: BreadcrumbItem[];
        children?: Snippet;
    } = $props();
</script>

<div class="flex min-h-screen flex-col bg-background">
    <AppTopbar />

    <main class="mx-auto w-full max-w-6xl flex-1 pb-24 md:pb-8">
        {@render children?.()}
    </main>

    <MobileBottomNav />
    <Toaster />
</div>
