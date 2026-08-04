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
    import Heading from '@/components/Heading.svelte';
</script>

<AppHead title="إعدادات المظهر" />

<h1 class="sr-only">إعدادات المظهر</h1>

<div class="space-y-6">
    <Heading
        variant="small"
        title="إعدادات المظهر"
        description="تحديث إعدادات المظهر لحسابك"
    />
    <AppearanceTabs />
</div>
