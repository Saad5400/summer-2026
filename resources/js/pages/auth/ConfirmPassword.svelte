<script module lang="ts">
    export const layout = {
        title: 'تأكيد كلمة المرور',
        description:
            'هذه منطقة آمنة من التطبيق. يرجى تأكيد كلمة المرور للمتابعة.',
    };
</script>

<script lang="ts">
    import { Form } from '@inertiajs/svelte';
    import Lock from 'lucide-svelte/icons/lock';
    import {
        index as confirmOptions,
        store as confirmStore,
    } from '@/actions/Laravel/Passkeys/Http/Controllers/PasskeyConfirmationController';
    import AppHead from '@/components/AppHead.svelte';
    import InputError from '@/components/InputError.svelte';
    import PasskeyVerify from '@/components/PasskeyVerify.svelte';
    import PasswordInput from '@/components/PasswordInput.svelte';
    import { Button } from '@/components/ui/button';
    import { Label } from '@/components/ui/label';
    import { Spinner } from '@/components/ui/spinner';
    import { store } from '@/routes/password/confirm';
</script>

<AppHead title="تأكيد كلمة المرور" />

<PasskeyVerify
    routes={{
        options: confirmOptions(),
        submit: confirmStore(),
    }}
    label="تأكيد بمفتاح المرور"
    loadingLabel="جار التأكيد..."
    separator="أو تأكيد بكلمة المرور"
/>

<Form {...store.form()} resetOnSuccess>
    {#snippet children({ errors, processing })}
        <div class="space-y-6">
            <div class="grid gap-2">
                <Label for="password">كلمة المرور</Label>
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

            <Button
                type="submit"
                class="h-11 w-full rounded-xl text-sm font-semibold shadow-soft transition-transform active:scale-[0.98]"
                disabled={processing}
                data-test="confirm-password-button"
            >
                {#if processing}<Spinner />{/if}
                تأكيد كلمة المرور
            </Button>
        </div>
    {/snippet}
</Form>
