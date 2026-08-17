<script module lang="ts">
    export const layout = {
        title: 'نسيت كلمة المرور',
        description:
            'أدخل بريدك الإلكتروني لاستلام رابط إعادة تعيين كلمة المرور',
    };
</script>

<script lang="ts">
    import { Form } from '@inertiajs/svelte';
    import Mail from 'lucide-svelte/icons/mail';
    import AppHead from '@/components/AppHead.svelte';
    import InputError from '@/components/InputError.svelte';
    import TextLink from '@/components/TextLink.svelte';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Spinner } from '@/components/ui/spinner';
    import { login } from '@/routes';
    import { email } from '@/routes/password';

    let {
        status = '',
    }: {
        status?: string;
    } = $props();
</script>

<AppHead title="نسيت كلمة المرور" />

{#if status}
    <div
        class="mb-6 rounded-xl border border-income/25 bg-income-muted/60 px-4 py-3 text-center text-sm font-medium text-income"
    >
        {status}
    </div>
{/if}

<div class="space-y-6">
    <Form {...email.form()} class="flex flex-col gap-6">
        {#snippet children({ errors, processing })}
            <div class="grid gap-2">
                <Label for="email">البريد الإلكتروني</Label>
                <div class="relative">
                    <Mail
                        class="pointer-events-none absolute start-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        autocomplete="off"
                        inputmode="email"
                        placeholder="email@example.com"
                        class="h-11 rounded-xl ps-10 text-base md:text-sm"
                    />
                </div>
                <InputError message={errors.email} />
            </div>

            <Button
                type="submit"
                class="h-11 w-full rounded-xl text-sm font-semibold shadow-soft transition-transform active:scale-[0.98]"
                disabled={processing}
                data-test="email-password-reset-link-button"
            >
                {#if processing}<Spinner />{/if}
                إرسال رابط إعادة تعيين كلمة المرور
            </Button>
        {/snippet}
    </Form>

    <div class="text-center text-sm text-muted-foreground">
        <span>أو العودة إلى</span>
        <TextLink href={login()}>تسجيل الدخول</TextLink>
    </div>
</div>
