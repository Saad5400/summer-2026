<script module lang="ts">
    export const layout = {
        title: 'إعادة تعيين كلمة المرور',
        description: 'يرجى إدخال كلمة المرور الجديدة أدناه',
    };
</script>

<script lang="ts">
    import { Form } from '@inertiajs/svelte';
    import Lock from 'lucide-svelte/icons/lock';
    import Mail from 'lucide-svelte/icons/mail';
    import ShieldCheck from 'lucide-svelte/icons/shield-check';
    import AppHead from '@/components/AppHead.svelte';
    import InputError from '@/components/InputError.svelte';
    import PasswordInput from '@/components/PasswordInput.svelte';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Spinner } from '@/components/ui/spinner';
    import { update } from '@/routes/password';

    let {
        token,
        email,
        passwordRules,
    }: {
        token: string;
        email: string;
        passwordRules: string;
    } = $props();
</script>

<AppHead title="إعادة تعيين كلمة المرور" />

<Form
    {...update.form()}
    transform={(data) => ({ ...data, token, email })}
    resetOnSuccess={['password', 'password_confirmation']}
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
                        autocomplete="email"
                        value={email}
                        readonly
                        class="h-11 rounded-xl bg-muted/50 ps-10 text-base md:text-sm"
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
                        name="password"
                        autocomplete="new-password"
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
                        name="password_confirmation"
                        autocomplete="new-password"
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
                data-test="reset-password-button"
            >
                {#if processing}<Spinner />{/if}
                إعادة تعيين كلمة المرور
            </Button>
        </div>
    {/snippet}
</Form>
