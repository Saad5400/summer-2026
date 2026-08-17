<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import type { Snippet } from 'svelte';
    import AuthSimpleLayout from '@/layouts/auth/AuthSimpleLayout.svelte';
    import AuthSplitLayout from '@/layouts/auth/AuthSplitLayout.svelte';

    let {
        title = '',
        description = '',
        children,
    }: {
        title?: string;
        description?: string;
        children?: Snippet;
    } = $props();

    // Login & Register get the premium split experience on desktop
    // (it collapses to the same centered card on mobile); every other
    // auth page uses the focused centered layout.
    const splitPages = ['auth/Login', 'auth/Register'];
    const Layout = $derived(
        splitPages.includes(page.component)
            ? AuthSplitLayout
            : AuthSimpleLayout,
    );
</script>

<Layout {title} {description}>
    {@render children?.()}
</Layout>
