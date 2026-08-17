<script lang="ts">
    import { Link, page } from '@inertiajs/svelte';
    import ArrowLeftRight from 'lucide-svelte/icons/arrow-left-right';
    import BarChart3 from 'lucide-svelte/icons/bar-chart-3';
    import Bot from 'lucide-svelte/icons/bot';
    import LayoutGrid from 'lucide-svelte/icons/layout-grid';
    import Moon from 'lucide-svelte/icons/moon';
    import Sun from 'lucide-svelte/icons/sun';
    import Tags from 'lucide-svelte/icons/tags';
    import {
        DropdownMenu,
        DropdownMenuContent,
        DropdownMenuTrigger,
    } from '@/components/ui/dropdown-menu';
    import UserInfo from '@/components/UserInfo.svelte';
    import UserMenuContent from '@/components/UserMenuContent.svelte';
    import { currentUrlState } from '@/lib/currentUrl.svelte';
    import { themeState } from '@/lib/theme.svelte';
    import { toUrl } from '@/lib/utils';
    import { assistant, dashboard } from '@/routes';
    import type { NavItem } from '@/types';

    const items: NavItem[] = [
        { title: 'الرئيسية', href: dashboard(), icon: LayoutGrid },
        { title: 'المعاملات', href: '/transactions', icon: ArrowLeftRight },
        { title: 'التقارير', href: '/reports', icon: BarChart3 },
        { title: 'الفئات', href: '/categories', icon: Tags },
        { title: 'المساعد المالي', href: assistant(), icon: Bot },
    ];

    const url = currentUrlState();
    const { resolvedAppearance, updateAppearance } = themeState();
    const user = $derived(page.props.auth.user);

    function toggleTheme() {
        updateAppearance(resolvedAppearance() === 'dark' ? 'light' : 'dark');
    }
</script>

<header
    class="sticky top-0 z-40 border-b border-border/60 bg-background/75 backdrop-blur-xl"
>
    <div
        class="mx-auto flex h-16 w-full max-w-6xl items-center gap-3 px-4 md:px-6"
    >
        <!-- Brand -->
        <Link
            href={toUrl(dashboard())}
            class="flex shrink-0 items-center gap-2"
            aria-label="ميزان"
        >
            <span
                class="flex size-9 items-center justify-center rounded-xl bg-primary text-primary-foreground shadow-soft"
            >
                <svg viewBox="0 0 32 32" fill="none" class="size-5">
                    <rect x="6" y="17" width="4.5" height="9" rx="2.25" fill="currentColor" opacity="0.55" />
                    <rect x="13.75" y="11" width="4.5" height="15" rx="2.25" fill="currentColor" opacity="0.8" />
                    <rect x="21.5" y="6" width="4.5" height="20" rx="2.25" fill="currentColor" />
                </svg>
            </span>
            <span class="text-lg font-bold tracking-tight">ميزان</span>
        </Link>

        <!-- Desktop nav -->
        <nav class="mx-2 hidden flex-1 items-center gap-1 md:flex">
            {#each items as item (toUrl(item.href))}
                {@const active = url.isCurrentUrl(item.href, url.currentUrl)}
                <Link
                    href={toUrl(item.href)}
                    class="relative rounded-full px-3.5 py-2 text-sm font-medium transition-colors duration-200 {active
                        ? 'bg-accent text-foreground'
                        : 'text-muted-foreground hover:bg-accent/60 hover:text-foreground'}"
                    aria-current={active ? 'page' : undefined}
                >
                    {item.title}
                </Link>
            {/each}
        </nav>

        <div class="flex flex-1 items-center justify-end gap-1 md:flex-none">
            <!-- Theme toggle -->
            <button
                type="button"
                onclick={toggleTheme}
                class="inline-flex size-9 items-center justify-center rounded-full text-muted-foreground transition-colors hover:bg-accent hover:text-foreground active:scale-95"
                aria-label="تبديل السمة"
            >
                <Sun class="size-[18px] dark:hidden" />
                <Moon class="hidden size-[18px] dark:block" />
            </button>

            <!-- User menu -->
            {#if user}
                <DropdownMenu>
                    <DropdownMenuTrigger
                        class="flex items-center gap-2 rounded-full p-1 transition-colors hover:bg-accent active:scale-95"
                        aria-label="حساب المستخدم"
                    >
                        <UserInfo {user} compact />
                    </DropdownMenuTrigger>
                    <DropdownMenuContent class="w-60 rounded-xl" align="end" sideOffset={8}>
                        <UserMenuContent {user} />
                    </DropdownMenuContent>
                </DropdownMenu>
            {/if}
        </div>
    </div>
</header>
