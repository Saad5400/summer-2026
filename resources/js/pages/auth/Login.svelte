<script module lang="ts">
    export const layout = {
        title: 'تسجيل الدخول إلى حسابك',
        description: 'أدخل بريدك الإلكتروني وكلمة المرور أدناه لتسجيل الدخول',
    };
</script>

<script lang="ts">
    import { Form } from '@inertiajs/svelte';
    import Lock from 'lucide-svelte/icons/lock';
    import Mail from 'lucide-svelte/icons/mail';
    import AppHead from '@/components/AppHead.svelte';
    import InputError from '@/components/InputError.svelte';
    import PasskeyVerify from '@/components/PasskeyVerify.svelte';
    import PasswordInput from '@/components/PasswordInput.svelte';
    import TextLink from '@/components/TextLink.svelte';
    import { Button } from '@/components/ui/button';
    import { Checkbox } from '@/components/ui/checkbox';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Spinner } from '@/components/ui/spinner';
    import { register } from '@/routes';
    import { store } from '@/routes/login';
    import { request } from '@/routes/password';

    let {
        status = '',
        canResetPassword,
    }: {
        status?: string;
        canResetPassword: boolean;
    } = $props();
</script>

<AppHead title="تسجيل الدخول" />

{#if status}
    <div
        class="mb-6 rounded-xl border border-income/25 bg-income-muted/60 px-4 py-3 text-center text-sm font-medium text-income"
    >
        {status}
    </div>
{/if}

<PasskeyVerify />

<Form
    {...store.form()}
    resetOnSuccess={['password']}
    class="flex flex-col gap-6"
>
    {#snippet children({ errors, processing })}
        <div class="grid gap-5">
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
                        required
                        autocomplete="email"
                        inputmode="email"
                        placeholder="email@example.com"
                        class="h-11 rounded-xl ps-10 text-base md:text-sm"
                    />
                </div>
                <InputError message={errors.email} />
            </div>

            <div class="grid gap-2">
                <div class="flex items-center justify-between">
                    <Label for="password">كلمة المرور</Label>
                    {#if canResetPassword}
                        <TextLink href={request()} class="text-sm">
                            نسيت كلمة المرور؟
                        </TextLink>
                    {/if}
                </div>
                <div class="relative">
                    <Lock
                        class="pointer-events-none absolute start-3 top-1/2 z-10 size-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <PasswordInput
                        id="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="كلمة المرور"
                        class="h-11 rounded-xl ps-10 text-base md:text-sm"
                    />
                </div>
                <InputError message={errors.password} />
            </div>

            <Label
                for="remember"
                class="flex w-fit items-center gap-2.5 text-sm font-normal text-muted-foreground"
            >
                <Checkbox id="remember" name="remember" />
                <span>تذكرني</span>
            </Label>

            <Button
                type="submit"
                class="mt-1 h-11 w-full rounded-xl text-sm font-semibold shadow-soft transition-transform active:scale-[0.98]"
                disabled={processing}
                data-test="login-button"
            >
                {#if processing}<Spinner />{/if}
                تسجيل الدخول
            </Button>
        </div>

        <div class="text-center text-sm text-muted-foreground">
            ليس لديك حساب؟
            <TextLink href={register()}>إنشاء حساب</TextLink>
        </div>
    {/snippet}
</Form>
