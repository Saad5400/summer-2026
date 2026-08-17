<script module lang="ts">
    import { edit } from '@/routes/security';

    export const layout = {
        breadcrumbs: [
            {
                title: 'إعدادات الأمان',
                href: edit(),
            },
        ],
    };
</script>

<script lang="ts">
    import { Form, page } from '@inertiajs/svelte';
    import Check from 'lucide-svelte/icons/check';
    import SecurityController from '@/actions/App/Http/Controllers/Settings/SecurityController';
    import AppHead from '@/components/AppHead.svelte';
    import InputError from '@/components/InputError.svelte';
    import ManagePasskeys from '@/components/ManagePasskeys.svelte';
    import type { Props as ManagePasskeysProps } from '@/components/ManagePasskeys.svelte';
    import ManageTwoFactor from '@/components/ManageTwoFactor.svelte';
    import PasswordInput from '@/components/PasswordInput.svelte';
    import { Button } from '@/components/ui/button';
    import {
        Card,
        CardContent,
        CardDescription,
        CardHeader,
        CardTitle,
    } from '@/components/ui/card';
    import { Label } from '@/components/ui/label';
    const canManageTwoFactor = $derived(Boolean(page.props.canManageTwoFactor));
    const requiresConfirmation = $derived(
        Boolean(page.props.requiresConfirmation),
    );
    const twoFactorEnabled = $derived(Boolean(page.props.twoFactorEnabled));
    const canManagePasskeys = $derived(Boolean(page.props.canManagePasskeys));
    const passkeys = $derived(
        (Array.isArray(page.props.passkeys)
            ? page.props.passkeys
            : []) as ManagePasskeysProps['passkeys'],
    );

    let { passwordRules }: { passwordRules: string } = $props();
</script>

<AppHead title="إعدادات الأمان" />

<h1 class="sr-only">إعدادات الأمان</h1>

<div class="space-y-6">
    <!-- Password -->
    <Card class="animate-fade-in-up p-6">
        <CardHeader class="p-0">
            <CardTitle>كلمة المرور</CardTitle>
            <CardDescription>
                تأكد من أن حسابك يستخدم كلمة مرور طويلة وعشوائية للبقاء آمناً
            </CardDescription>
        </CardHeader>

        <CardContent class="p-0">
            <Form
                {...SecurityController.update.form()}
                class="space-y-6"
                options={{ preserveScroll: true }}
                resetOnSuccess
                resetOnError={[
                    'password',
                    'password_confirmation',
                    'current_password',
                ]}
            >
                {#snippet children({ errors, processing, recentlySuccessful })}
                    <div class="grid gap-2">
                        <Label for="current_password">
                            كلمة المرور الحالية
                        </Label>
                        <PasswordInput
                            id="current_password"
                            name="current_password"
                            class="block w-full"
                            autocomplete="current-password"
                            placeholder="كلمة المرور الحالية"
                        />
                        <InputError message={errors.current_password} />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password">كلمة المرور الجديدة</Label>
                        <PasswordInput
                            id="password"
                            name="password"
                            class="block w-full"
                            autocomplete="new-password"
                            placeholder="كلمة المرور الجديدة"
                            passwordrules={passwordRules}
                        />
                        <InputError message={errors.password} />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation">
                            تأكيد كلمة المرور
                        </Label>
                        <PasswordInput
                            id="password_confirmation"
                            name="password_confirmation"
                            class="block w-full"
                            autocomplete="new-password"
                            placeholder="تأكيد كلمة المرور"
                            passwordrules={passwordRules}
                        />
                        <InputError message={errors.password_confirmation} />
                    </div>

                    <div class="flex items-center gap-3">
                        <Button
                            type="submit"
                            disabled={processing}
                            data-test="update-password-button"
                        >
                            حفظ
                        </Button>

                        {#if recentlySuccessful}
                            <span
                                class="flex items-center gap-1.5 text-sm text-muted-foreground animate-fade-in-up"
                            >
                                <Check class="size-4 text-primary" />
                                تم الحفظ
                            </span>
                        {/if}
                    </div>
                {/snippet}
            </Form>
        </CardContent>
    </Card>

    <!-- Two-factor authentication -->
    {#if canManageTwoFactor}
        <Card class="animate-fade-in-up p-6">
            <ManageTwoFactor
                {canManageTwoFactor}
                {requiresConfirmation}
                {twoFactorEnabled}
            />
        </Card>
    {/if}

    <!-- Passkeys -->
    {#if canManagePasskeys}
        <Card class="animate-fade-in-up p-6">
            <ManagePasskeys {canManagePasskeys} {passkeys} />
        </Card>
    {/if}
</div>
