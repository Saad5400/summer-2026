<script lang="ts">
    let {
        class: className = '',
        ...rest
    }: {
        class?: string;
        [key: string]: unknown;
    } = $props();
</script>

<!-- Mizan mark: a balanced scale / ascending bars motif -->
<svg
    xmlns="http://www.w3.org/2000/svg"
    viewBox="0 0 32 32"
    fill="none"
    class={className}
    {...rest}
>
    <rect x="6" y="17" width="4.5" height="9" rx="2.25" fill="currentColor" opacity="0.55" />
    <rect x="13.75" y="11" width="4.5" height="15" rx="2.25" fill="currentColor" opacity="0.8" />
    <rect x="21.5" y="6" width="4.5" height="20" rx="2.25" fill="currentColor" />
</svg>
