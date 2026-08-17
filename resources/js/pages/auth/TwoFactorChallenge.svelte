<script lang="ts">
    import { Form, setLayoutProps } from '@inertiajs/svelte';
    import ShieldCheck from 'lucide-svelte/icons/shield-check';
    import AppHead from '@/components/AppHead.svelte';
    import InputError from '@/components/InputError.svelte';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import {
        InputOTP,
        InputOTPGroup,
        InputOTPSlot,
    } from '@/components/ui/input-otp';
    import { store } from '@/routes/two-factor/login';
    import type { TwoFactorConfigContent } from '@/types';

    let showRecoveryInput = $state(false);
    let code = $state('');

    const authConfigContent: TwoFactorConfigContent = $derived.by(() => {
        if (showRecoveryInput) {
            return {
                title: 'رمز الاسترداد',
                description:
                    'يرجى تأكيد الوصول إلى حسابك بإدخال أحد رموز الاسترداد الطارئة.',
                buttonText: 'تسجيل الدخول باستخدام رمز التحقق',
            };
        }

        return {
            title: 'رمز التحقق',
            description: 'أدخل رمز التحقق المقدم من تطبيق المصادقة الخاص بك.',
            buttonText: 'تسجيل الدخول باستخدام رمز استرداد',
        };
    });

    $effect(() => {
        setLayoutProps({
            title: authConfigContent.title,
            description: authConfigContent.description,
        });
    });

    function toggleRecoveryMode(clearErrors: () => void) {
        showRecoveryInput = !showRecoveryInput;
        clearErrors();
        code = '';
    }
</script>

<AppHead title="التحقق بخطوتين" />

<div class="mb-6 flex justify-center">
    <div
        class="flex size-14 items-center justify-center rounded-2xl bg-primary/10 text-primary"
    >
        <ShieldCheck class="size-7" />
    </div>
</div>

<div class="space-y-6">
    {#if !showRecoveryInput}
        <Form
            {...store.form()}
            class="space-y-5"
            resetOnError
            onError={() => (code = '')}
        >
            {#snippet children({ errors, processing, clearErrors })}
                <input type="hidden" name="code" value={code} />
                <div
                    class="flex flex-col items-center justify-center space-y-3 text-center"
                >
                    <div class="flex w-full justify-center" dir="ltr">
                        <InputOTP
                            id="otp"
                            bind:value={code}
                            maxlength={6}
                            disabled={processing}
                            autofocus
                        >
                            <InputOTPGroup class="gap-2">
                                {#each { length: 6 } as _, i (i)}
                                    <InputOTPSlot
                                        index={i}
                                        class="size-12 rounded-lg border text-lg font-semibold first:rounded-lg last:rounded-lg"
                                    />
                                {/each}
                            </InputOTPGroup>
                        </InputOTP>
                    </div>
                    <InputError message={errors.code} />
                </div>
                <Button
                    type="submit"
                    class="h-11 w-full rounded-xl text-sm font-semibold shadow-soft transition-transform active:scale-[0.98]"
                    disabled={processing}
                >
                    متابعة
                </Button>
                <div class="text-center text-sm text-muted-foreground">
                    <span>أو يمكنك </span>
                    <button
                        type="button"
                        class="font-medium text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                        onclick={() => toggleRecoveryMode(clearErrors)}
                    >
                        {authConfigContent.buttonText}
                    </button>
                </div>
            {/snippet}
        </Form>
    {:else}
        <Form {...store.form()} class="space-y-5" resetOnError>
            {#snippet children({ errors, processing, clearErrors })}
                <Input
                    name="recovery_code"
                    type="text"
                    placeholder="أدخل رمز الاسترداد"
                    required
                    class="h-11 rounded-xl text-center text-base md:text-sm"
                />
                <InputError message={errors.recovery_code} />
                <Button
                    type="submit"
                    class="h-11 w-full rounded-xl text-sm font-semibold shadow-soft transition-transform active:scale-[0.98]"
                    disabled={processing}
                >
                    متابعة
                </Button>

                <div class="text-center text-sm text-muted-foreground">
                    <span>أو يمكنك </span>
                    <button
                        type="button"
                        class="font-medium text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                        onclick={() => toggleRecoveryMode(clearErrors)}
                    >
                        {authConfigContent.buttonText}
                    </button>
                </div>
            {/snippet}
        </Form>
    {/if}
</div>
