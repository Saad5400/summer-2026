<script lang="ts">
    let {
        title,
        description = '',
        variant = 'default',
    }: {
        title: string;
        description?: string;
        variant?: 'default' | 'small';
    } = $props();
</script>

<header class={variant === 'small' ? 'space-y-1' : 'mb-8 space-y-1'}>
    <h2
        class={variant === 'small'
            ? 'text-base font-semibold tracking-tight text-foreground'
            : 'text-xl font-semibold tracking-tight text-foreground'}
    >
        {title}
    </h2>
    {#if description}
        <p class="text-sm leading-relaxed text-muted-foreground">
            {description}
        </p>
    {/if}
</header>
