<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import Palette from 'lucide-svelte/icons/palette';
    import ShieldCheck from 'lucide-svelte/icons/shield-check';
    import User from 'lucide-svelte/icons/user';
    import type { Component, Snippet, SvelteComponent } from 'svelte';
    import { currentUrlState } from '@/lib/currentUrl.svelte';
    import { toUrl } from '@/lib/utils';
    import { edit as editAppearance } from '@/routes/appearance';
    import { edit as editProfile } from '@/routes/profile';
    import { edit as editSecurity } from '@/routes/security';
    import type { NavItem } from '@/types';

    let {
        children,
    }: {
        children?: Snippet;
    } = $props();

    type IconComponent =
        | Component<{ class?: string }>
        | (new (...args: any[]) => SvelteComponent<{ class?: string }>);

    const navItems: (NavItem & { icon: IconComponent })[] = [
        {
            title: 'الملف الشخصي',
            href: editProfile(),
            icon: User,
        },
        {
            title: 'الأمان',
            href: editSecurity(),
            icon: ShieldCheck,
        },
        {
            title: 'المظهر',
            href: editAppearance(),
            icon: Palette,
        },
    ];

    const url = currentUrlState();
</script>

<div class="px-4 py-6 sm:px-6 lg:py-10">
    <div class="mx-auto w-full max-w-4xl">
        <header class="mb-6 animate-fade-in-up lg:mb-8">
            <h1 class="text-2xl font-semibold tracking-tight text-foreground">
                الإعدادات
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">
                إدارة ملفك الشخصي وتفضيلات حسابك
            </p>
        </header>

        <!-- Mobile: horizontal scrollable segmented control -->
        <nav
            class="mb-6 lg:hidden"
            aria-label="أقسام الإعدادات"
        >
            <div
                class="-mx-4 overflow-x-auto px-4 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
            >
                <div
                    class="inline-flex min-w-full gap-1 rounded-xl border border-border bg-muted/60 p-1"
                >
                    {#each navItems as item (toUrl(item.href))}
                        {@const active = url.isCurrentUrl(
                            item.href,
                            url.currentUrl,
                        )}
                        {@const Icon = item.icon}
                        <Link
                            href={toUrl(item.href)}
                            aria-current={active ? 'page' : undefined}
                            class="flex flex-1 items-center justify-center gap-2 whitespace-nowrap rounded-lg px-4 py-2 text-sm font-medium transition-all active:scale-95 {active
                                ? 'bg-card text-foreground shadow-soft'
                                : 'text-muted-foreground hover:text-foreground'}"
                        >
                            <Icon
                                class="size-4 shrink-0 {active
                                    ? 'text-primary'
                                    : ''}"
                            />
                            <span>{item.title}</span>
                        </Link>
                    {/each}
                </div>
            </div>
        </nav>

        <div class="flex flex-col gap-8 lg:flex-row lg:gap-12">
            <!-- Desktop: vertical nav on the start side (right in RTL) -->
            <aside class="hidden shrink-0 lg:block lg:w-56">
                <nav
                    class="sticky top-8 flex flex-col gap-1"
                    aria-label="أقسام الإعدادات"
                >
                    {#each navItems as item (toUrl(item.href))}
                        {@const active = url.isCurrentUrl(
                            item.href,
                            url.currentUrl,
                        )}
                        {@const Icon = item.icon}
                        <Link
                            href={toUrl(item.href)}
                            aria-current={active ? 'page' : undefined}
                            class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors {active
                                ? 'bg-muted text-foreground'
                                : 'text-muted-foreground hover:bg-muted/60 hover:text-foreground'}"
                        >
                            <Icon
                                class="size-4 shrink-0 transition-colors {active
                                    ? 'text-primary'
                                    : 'text-muted-foreground group-hover:text-foreground'}"
                            />
                            <span>{item.title}</span>
                        </Link>
                    {/each}
                </nav>
            </aside>

            <div class="min-w-0 flex-1">
                <section class="max-w-2xl space-y-6">
                    {@render children?.()}
                </section>
            </div>
        </div>
    </div>
</div>
