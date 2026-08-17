<script module lang="ts">
    export const layout = {
        title: 'إنشاء حساب',
        description: 'أدخل بياناتك أدناه لإنشاء حسابك',
    };
</script>

<script lang="ts">
    import { Form } from '@inertiajs/svelte';
    import Lock from 'lucide-svelte/icons/lock';
    import Mail from 'lucide-svelte/icons/mail';
    import ShieldCheck from 'lucide-svelte/icons/shield-check';
    import User from 'lucide-svelte/icons/user';
    import AppHead from '@/components/AppHead.svelte';
    import InputError from '@/components/InputError.svelte';
    import PasswordInput from '@/components/PasswordInput.svelte';
    import TextLink from '@/components/TextLink.svelte';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Spinner } from '@/components/ui/spinner';
    import { login } from '@/routes';
    import { store } from '@/routes/register';

    let { passwordRules }: { passwordRules: string } = $props();
</script>

<AppHead title="إنشاء حساب" />

<Form
    {...store.form()}
    resetOnSuccess={['password', 'password_confirmation']}
    class="flex flex-col gap-6"
>
    {#snippet children({ errors, processing })}
        <div class="grid gap-5">
            <div class="grid gap-2">
                <Label for="name">الاسم</Label>
                <div class="relative">
                    <User
                        class="pointer-events-none absolute start-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        id="name"
                        type="text"
                        required
                        autocomplete="name"
                        name="name"
                        placeholder="الاسم الكامل"
                        class="h-11 rounded-xl ps-10 text-base md:text-sm"
                    />
                </div>
                <InputError message={errors.name} />
            </div>

            <div class="grid gap-2">
                <Label for="email">البريد الإلكتروني</Label>
                <div class="relative">
                    <Mail
                        class="pointer-events-none absolute start-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        id="email"
                        type="email"
                        required
                        autocomplete="email"
                        inputmode="email"
                        name="email"
                        placeholder="email@example.com"
                        class="h-11 rounded-xl ps-10 text-base md:text-sm"
                    />
                </div>
                <InputError message={errors.email} />
            </div>

            <div class="grid gap-2">
                <Label for="password">كلمة المرور</Label>
                <div class="relative">
                    <Lock
                        class="pointer-events-none absolute start-3 top-1/2 z-10 size-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <PasswordInput
                        id="password"
                        required
                        autocomplete="new-password"
                        name="password"
                        placeholder="كلمة المرور"
                        passwordrules={passwordRules}
                        class="h-11 rounded-xl ps-10 text-base md:text-sm"
                    />
                </div>
                <InputError message={errors.password} />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation">تأكيد كلمة المرور</Label>
                <div class="relative">
                    <ShieldCheck
                        class="pointer-events-none absolute start-3 top-1/2 z-10 size-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <PasswordInput
                        id="password_confirmation"
                        required
                        autocomplete="new-password"
                        name="password_confirmation"
                        placeholder="تأكيد كلمة المرور"
                        passwordrules={passwordRules}
                        class="h-11 rounded-xl ps-10 text-base md:text-sm"
                    />
                </div>
                <InputError message={errors.password_confirmation} />
            </div>

            <Button
                type="submit"
                class="mt-1 h-11 w-full rounded-xl text-sm font-semibold shadow-soft transition-transform active:scale-[0.98]"
                disabled={processing}
                data-test="register-user-button"
            >
                {#if processing}<Spinner />{/if}
                إنشاء حساب
            </Button>
        </div>

        <div class="text-center text-sm text-muted-foreground">
            لديك حساب بالفعل؟
            <TextLink href={login()}>تسجيل الدخول</TextLink>
        </div>
    {/snippet}
</Form>
