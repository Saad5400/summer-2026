<script lang="ts">
    import { renderMarkdown } from '@/lib/markdown';

    let { source }: { source: string } = $props();

    let html = $derived(renderMarkdown(source));
</script>

<div
    class="min-w-0
        [&_a]:font-medium [&_a]:text-primary [&_a]:underline
        [&_blockquote]:border-s-2 [&_blockquote]:ps-3 [&_blockquote]:text-muted-foreground
        [&_code]:rounded [&_code]:bg-muted [&_code]:px-1.5 [&_code]:py-0.5 [&_code]:font-mono [&_code]:text-[0.8125rem]
        [&_del]:text-muted-foreground
        [&_h1]:mb-1 [&_h1]:mt-2 [&_h1]:text-base [&_h1]:font-semibold
        [&_h2]:mb-1 [&_h2]:mt-2 [&_h2]:font-semibold
        [&_h3]:mb-1 [&_h3]:mt-2 [&_h3]:font-semibold
        [&_hr]:my-2 [&_hr]:border-border
        [&_li]:marker:text-muted-foreground [&_li]:my-0.5
        [&_ol]:list-decimal [&_ol]:ps-5
        [&_ol:first-child]:mt-0 [&_ol:last-child]:mb-0
        [&_p]:my-1.5
        [&_p:first-child]:mt-0 [&_p:last-child]:mb-0
        [&_pre]:mt-2 [&_pre]:overflow-x-auto [&_pre]:rounded-lg [&_pre]:bg-muted [&_pre]:p-3
        [&_pre:first-child]:mt-0 [&_pre:last-child]:mb-0
        [&_pre_code]:bg-transparent [&_pre_code]:p-0 [&_pre_code]:text-xs
        [&_strong]:font-semibold
        [&_table]:my-2 [&_table]:w-full [&_table]:border-collapse
        [&_td]:border [&_td]:px-2 [&_td]:py-1 [&_td]:text-start
        [&_th]:border [&_th]:bg-muted/50 [&_th]:px-2 [&_th]:py-1 [&_th]:text-start [&_th]:font-medium
        [&_ul]:list-disc [&_ul]:ps-5
        [&_ul:first-child]:mt-0 [&_ul:last-child]:mb-0"
    dir="auto"
>
    <!-- eslint-disable-next-line svelte/no-at-html-tags -- sanitized with DOMPurify in renderMarkdown -->
    {@html html}
</div>
