<script lang="ts">
    import {
        Avatar,
        AvatarFallback,
        AvatarImage,
    } from '@/components/ui/avatar';
    import { getInitials } from '@/lib/initials';
    import type { User } from '@/types';

    let {
        user,
        showEmail = false,
        compact = false,
    }: {
        user: User;
        showEmail?: boolean;
        compact?: boolean;
    } = $props();

    const showAvatar = $derived(user.avatar && user.avatar !== '');
</script>

<Avatar class="size-8 overflow-hidden rounded-full">
    {#if showAvatar}
        <AvatarImage src={user.avatar!} alt={user.name} />
    {/if}
    <AvatarFallback
        class="rounded-full bg-primary/12 text-sm font-semibold text-primary"
    >
        {getInitials(user.name)}
    </AvatarFallback>
</Avatar>

{#if !compact}
    <div class="grid flex-1 text-start text-sm leading-tight">
        <span class="truncate font-medium">{user.name}</span>
        {#if showEmail}
            <span class="truncate text-xs text-muted-foreground"
                >{user.email}</span
            >
        {/if}
    </div>
{/if}
