<script module lang="ts">
    import { edit as editAppearance } from '@/routes/appearance';

    export const layout = {
        breadcrumbs: [
            {
                title: 'إعدادات المظهر',
                href: editAppearance(),
            },
        ],
    };
</script>

<script lang="ts">
    import AppearanceTabs from '@/components/AppearanceTabs.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import {
        Card,
        CardContent,
        CardDescription,
        CardHeader,
        CardTitle,
    } from '@/components/ui/card';
</script>

<AppHead title="إعدادات المظهر" />

<h1 class="sr-only">إعدادات المظهر</h1>

<Card class="animate-fade-in-up p-6">
    <CardHeader class="p-0">
        <CardTitle>المظهر</CardTitle>
        <CardDescription>
            اختر مظهر التطبيق أو اتركه ليتبع إعدادات نظامك
        </CardDescription>
    </CardHeader>
    <CardContent class="p-0">
        <AppearanceTabs />
    </CardContent>
</Card>
