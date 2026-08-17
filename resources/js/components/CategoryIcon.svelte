<script lang="ts">
    import BookOpen from 'lucide-svelte/icons/book-open';
    import Briefcase from 'lucide-svelte/icons/briefcase';
    import Car from 'lucide-svelte/icons/car';
    import Ellipsis from 'lucide-svelte/icons/ellipsis';
    import Gamepad2 from 'lucide-svelte/icons/gamepad-2';
    import Gift from 'lucide-svelte/icons/gift';
    import Heart from 'lucide-svelte/icons/heart';
    import House from 'lucide-svelte/icons/house';
    import Laptop from 'lucide-svelte/icons/laptop';
    import Receipt from 'lucide-svelte/icons/receipt';
    import ShoppingBag from 'lucide-svelte/icons/shopping-bag';
    import TrendingUp from 'lucide-svelte/icons/trending-up';
    import Utensils from 'lucide-svelte/icons/utensils';
    import Wallet from 'lucide-svelte/icons/wallet';
    import type { Component } from 'svelte';
    import { cn } from '@/lib/utils';

    let {
        icon,
        color = null,
        size = 'md',
        class: className = '',
    }: {
        icon: string | null | undefined;
        color?: string | null;
        size?: 'sm' | 'md' | 'lg';
        class?: string;
    } = $props();

    const map: Record<string, Component<{ class?: string }>> = {
        utensils: Utensils,
        car: Car,
        home: House,
        house: House,
        receipt: Receipt,
        gamepad: Gamepad2,
        'gamepad-2': Gamepad2,
        'shopping-bag': ShoppingBag,
        heart: Heart,
        'book-open': BookOpen,
        'more-horizontal': Ellipsis,
        ellipsis: Ellipsis,
        briefcase: Briefcase,
        laptop: Laptop,
        'trending-up': TrendingUp,
        gift: Gift,
        wallet: Wallet,
    };

    const Icon = $derived(map[icon ?? ''] ?? Wallet);

    const box = {
        sm: 'size-8 rounded-lg',
        md: 'size-10 rounded-xl',
        lg: 'size-12 rounded-2xl',
    } as const;
    const glyph = {
        sm: 'size-4',
        md: 'size-5',
        lg: 'size-6',
    } as const;

    const tint = $derived(color ?? 'var(--primary)');
</script>

<span
    class={cn(
        'flex shrink-0 items-center justify-center',
        box[size],
        className,
    )}
    style="background-color: color-mix(in oklch, {tint} 14%, transparent); color: {tint};"
>
    <Icon class={glyph[size]} />
</span>
