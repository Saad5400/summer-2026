<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import ArrowLeftRight from 'lucide-svelte/icons/arrow-left-right';
    import BarChart3 from 'lucide-svelte/icons/bar-chart-3';
    import Bot from 'lucide-svelte/icons/bot';
    import LayoutGrid from 'lucide-svelte/icons/layout-grid';
    import Tags from 'lucide-svelte/icons/tags';
    import { currentUrlState } from '@/lib/currentUrl.svelte';
    import { toUrl } from '@/lib/utils';
    import { assistant, dashboard } from '@/routes';
    import type { NavItem } from '@/types';

    const items: NavItem[] = [
        { title: 'الرئيسية', href: dashboard(), icon: LayoutGrid },
        { title: 'المعاملات', href: '/transactions', icon: ArrowLeftRight },
        { title: 'التقارير', href: '/reports', icon: BarChart3 },
        { title: 'الفئات', href: '/categories', icon: Tags },
        { title: 'المساعد', href: assistant(), icon: Bot },
    ];

    const url = currentUrlState();
</script>

<nav
    class="fixed inset-x-0 bottom-0 z-40 border-t border-border/60 bg-background/80 backdrop-blur-xl md:hidden"
    style="padding-bottom: env(safe-area-inset-bottom);"
    aria-label="التنقل الرئيسي"
>
    <ul class="mx-auto flex max-w-md items-stretch justify-around px-1">
        {#each items as item (toUrl(item.href))}
            {@const active = url.isCurrentUrl(item.href, url.currentUrl)}
            <li class="flex-1">
                <Link
                    href={toUrl(item.href)}
                    class="group relative flex flex-col items-center gap-1 py-2.5 text-[11px] font-medium transition-colors duration-200 {active
                        ? 'text-primary'
                        : 'text-muted-foreground'}"
                    aria-current={active ? 'page' : undefined}
                >
                    <span
                        class="flex h-8 w-14 items-center justify-center rounded-full transition-all duration-300 {active
                            ? 'bg-primary/12'
                            : 'bg-transparent group-active:scale-90'}"
                    >
                        <item.icon
                            class="size-[22px] transition-transform duration-300 {active
                                ? 'scale-100'
                                : 'scale-95'}"
                        />
                    </span>
                    <span>{item.title}</span>
                </Link>
            </li>
        {/each}
    </ul>
</nav>
