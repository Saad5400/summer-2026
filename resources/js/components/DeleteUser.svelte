<script lang="ts">
    import { Form } from '@inertiajs/svelte';
    import TriangleAlert from 'lucide-svelte/icons/triangle-alert';
    import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
    import InputError from '@/components/InputError.svelte';
    import PasswordInput from '@/components/PasswordInput.svelte';
    import {
        AlertDialog,
        AlertDialogCancel,
        AlertDialogContent,
        AlertDialogDescription,
        AlertDialogHeader,
        AlertDialogTitle,
        AlertDialogTrigger,
    } from '@/components/ui/alert-dialog';
    import { Button } from '@/components/ui/button';
    import {
        Card,
        CardContent,
        CardDescription,
        CardHeader,
        CardTitle,
    } from '@/components/ui/card';
    import { Label } from '@/components/ui/label';
</script>

<Card
    class="animate-fade-in-up border-destructive/30 bg-destructive/[0.03] p-6 ring-destructive/20"
>
    <CardHeader class="p-0">
        <CardTitle class="flex items-center gap-2 text-destructive">
            <TriangleAlert class="size-4" />
            حذف الحساب
        </CardTitle>
        <CardDescription>حذف حسابك وجميع بياناته نهائياً</CardDescription>
    </CardHeader>

    <CardContent class="p-0">
        <div
            class="flex flex-col gap-4 rounded-lg border border-destructive/20 bg-destructive/5 p-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div class="space-y-1">
                <p class="text-sm font-medium text-destructive">
                    هذا الإجراء لا يمكن التراجع عنه
                </p>
                <p class="text-sm text-muted-foreground">
                    سيتم حذف جميع مواردك وبياناتك بشكل دائم.
                </p>
            </div>

            <AlertDialog>
                <AlertDialogTrigger>
                    {#snippet child({ props })}
                        <Button
                            variant="destructive"
                            class="shrink-0"
                            data-test="delete-user-button"
                            {...props}
                        >
                            حذف الحساب
                        </Button>
                    {/snippet}
                </AlertDialogTrigger>
                <AlertDialogContent>
                    <Form
                        {...ProfileController.destroy.form()}
                        class="space-y-5"
                        options={{ preserveScroll: true }}
                    >
                        {#snippet children({ errors, processing })}
                            <AlertDialogHeader>
                                <AlertDialogTitle>
                                    هل أنت متأكد من حذف حسابك؟
                                </AlertDialogTitle>
                                <AlertDialogDescription>
                                    بمجرد حذف حسابك، سيتم حذف جميع موارده
                                    وبياناته بشكل دائم. يرجى إدخال كلمة المرور
                                    لتأكيد رغبتك في حذف حسابك بشكل دائم.
                                </AlertDialogDescription>
                            </AlertDialogHeader>

                            <div class="grid gap-2">
                                <Label for="password" class="sr-only">
                                    كلمة المرور
                                </Label>
                                <PasswordInput
                                    id="password"
                                    name="password"
                                    placeholder="كلمة المرور"
                                />
                                <InputError message={errors.password} />
                            </div>

                            <div
                                class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end"
                            >
                                <AlertDialogCancel type="button">
                                    إلغاء
                                </AlertDialogCancel>
                                <Button
                                    type="submit"
                                    variant="destructive"
                                    disabled={processing}
                                    data-test="confirm-delete-user-button"
                                >
                                    حذف الحساب
                                </Button>
                            </div>
                        {/snippet}
                    </Form>
                </AlertDialogContent>
            </AlertDialog>
        </div>
    </CardContent>
</Card>
