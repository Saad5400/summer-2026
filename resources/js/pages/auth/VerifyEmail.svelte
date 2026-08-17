<script module lang="ts">
    export const layout = {
        title: 'تأكيد البريد الإلكتروني',
        description:
            'يرجى تأكيد بريدك الإلكتروني بالضغط على الرابط الذي أرسلناه إليك.',
    };
</script>

<script lang="ts">
    import { Form } from '@inertiajs/svelte';
    import MailCheck from 'lucide-svelte/icons/mail-check';
    import AppHead from '@/components/AppHead.svelte';
    import TextLink from '@/components/TextLink.svelte';
    import { Button } from '@/components/ui/button';
    import { Spinner } from '@/components/ui/spinner';
    import { logout } from '@/routes';
    import { send } from '@/routes/verification';

    let {
        status = '',
    }: {
        status?: string;
    } = $props();
</script>

<AppHead title="تأكيد البريد الإلكتروني" />

<div class="mb-6 flex justify-center">
    <div
        class="flex size-14 items-center justify-center rounded-2xl bg-primary/10 text-primary"
    >
        <MailCheck class="size-7" />
    </div>
</div>

{#if status === 'verification-link-sent'}
    <div
        class="mb-6 rounded-xl border border-income/25 bg-income-muted/60 px-4 py-3 text-center text-sm font-medium text-income"
    >
        تم إرسال رابط تأكيد جديد إلى البريد الإلكتروني الذي قدمته أثناء التسجيل.
    </div>
{/if}

<Form {...send.form()} class="space-y-4 text-center">
    {#snippet children({ processing })}
        <Button
            type="submit"
            class="h-11 w-full rounded-xl text-sm font-semibold transition-transform active:scale-[0.98]"
            disabled={processing}
            variant="secondary"
        >
            {#if processing}<Spinner />{/if}
            إعادة إرسال بريد التأكيد
        </Button>

        <TextLink href={logout()} as="button" class="mx-auto block text-sm">
            تسجيل الخروج
        </TextLink>
    {/snippet}
</Form>
