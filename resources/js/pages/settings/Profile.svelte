<script module lang="ts">
    import { edit } from '@/routes/profile';

    export const layout = {
        breadcrumbs: [
            {
                title: 'إعدادات الملف الشخصي',
                href: edit(),
            },
        ],
    };
</script>

<script lang="ts">
    import { Form, page } from '@inertiajs/svelte';
    import BadgeCheck from 'lucide-svelte/icons/badge-check';
    import Check from 'lucide-svelte/icons/check';
    import MailWarning from 'lucide-svelte/icons/mail-warning';
    import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
    import AppHead from '@/components/AppHead.svelte';
    import DeleteUser from '@/components/DeleteUser.svelte';
    import InputError from '@/components/InputError.svelte';
    import TextLink from '@/components/TextLink.svelte';
    import { Avatar, AvatarFallback } from '@/components/ui/avatar';
    import { Button } from '@/components/ui/button';
    import {
        Card,
        CardContent,
        CardDescription,
        CardHeader,
        CardTitle,
    } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { send } from '@/routes/verification';

    const user = $derived(page.props.auth.user);

    const initials = $derived(
        (user.name ?? '')
            .trim()
            .split(/\s+/)
            .slice(0, 2)
            .map((part: string) => part.charAt(0))
            .join('')
            .toUpperCase() || 'م',
    );

    const isVerified = $derived(Boolean(user.email_verified_at));
</script>

<AppHead title="إعدادات الملف الشخصي" />

<h1 class="sr-only">إعدادات الملف الشخصي</h1>

<div class="space-y-6">
    <Card class="animate-fade-in-up p-6">
        <CardHeader class="p-0">
            <CardTitle>الملف الشخصي</CardTitle>
            <CardDescription>
                تحديث اسمك وبريدك الإلكتروني
            </CardDescription>
        </CardHeader>

        <CardContent class="space-y-6 p-0">
            <!-- Avatar / initials block -->
            <div class="flex items-center gap-4">
                <Avatar class="size-14">
                    <AvatarFallback
                        class="bg-primary text-lg font-semibold text-primary-foreground"
                    >
                        {initials}
                    </AvatarFallback>
                </Avatar>
                <div class="min-w-0 space-y-1">
                    <p class="truncate font-medium text-foreground">
                        {user.name}
                    </p>
                    <div
                        class="flex items-center gap-1.5 text-sm text-muted-foreground"
                    >
                        <span class="truncate">{user.email}</span>
                        {#if isVerified}
                            <BadgeCheck
                                class="size-4 shrink-0 text-primary"
                            />
                        {/if}
                    </div>
                </div>
            </div>

            <Form
                {...ProfileController.update.form()}
                class="space-y-6"
                options={{ preserveScroll: true }}
            >
                {#snippet children({ errors, processing, recentlySuccessful })}
                    <div class="grid gap-2">
                        <Label for="name">الاسم</Label>
                        <Input
                            id="name"
                            name="name"
                            class="block w-full"
                            value={user.name}
                            required
                            autocomplete="name"
                            placeholder="الاسم الكامل"
                        />
                        <InputError message={errors.name} />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">البريد الإلكتروني</Label>
                        <Input
                            id="email"
                            type="email"
                            name="email"
                            class="block w-full"
                            value={user.email}
                            required
                            autocomplete="username"
                            placeholder="البريد الإلكتروني"
                        />
                        <InputError message={errors.email} />
                    </div>

                    {#if Boolean(page.props.mustVerifyEmail) && !user.email_verified_at}
                        <div
                            class="rounded-lg border border-amber-500/30 bg-amber-500/10 p-4"
                        >
                            <div class="flex items-start gap-3">
                                <MailWarning
                                    class="mt-0.5 size-5 shrink-0 text-amber-600 dark:text-amber-500"
                                />
                                <div class="space-y-1 text-sm">
                                    <p
                                        class="font-medium text-amber-700 dark:text-amber-400"
                                    >
                                        بريدك الإلكتروني غير مؤكد
                                    </p>
                                    <p
                                        class="text-amber-700/80 dark:text-amber-400/80"
                                    >
                                        <TextLink href={send()} as="button">
                                            اضغط هنا لإعادة إرسال بريد التأكيد.
                                        </TextLink>
                                    </p>
                                    {#if page.props.status === 'verification-link-sent'}
                                        <p
                                            class="font-medium text-green-600 dark:text-green-500"
                                        >
                                            تم إرسال رابط تأكيد جديد إلى بريدك
                                            الإلكتروني.
                                        </p>
                                    {/if}
                                </div>
                            </div>
                        </div>
                    {/if}

                    <div class="flex items-center gap-3">
                        <Button
                            type="submit"
                            disabled={processing}
                            data-test="update-profile-button"
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

    <DeleteUser />
</div>
