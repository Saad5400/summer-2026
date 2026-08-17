<script lang="ts">
    import Sparkles from 'lucide-svelte/icons/sparkles';
    import Send from 'lucide-svelte/icons/send';
    import CategoryIcon from '@/components/CategoryIcon.svelte';
</script>

<div class="relative mx-auto w-full max-w-md">
    <div
        class="overflow-hidden rounded-2xl border border-border bg-card shadow-elevated"
    >
        <!-- assistant header -->
        <div
            class="flex items-center gap-3 border-b border-border bg-muted/40 px-4 py-3"
        >
            <span
                class="flex size-9 items-center justify-center rounded-xl bg-primary text-primary-foreground"
            >
                <Sparkles class="size-4.5" />
            </span>
            <div>
                <p class="text-sm font-semibold text-foreground">
                    المساعد المالي
                </p>
                <p
                    class="flex items-center gap-1.5 text-xs text-muted-foreground"
                >
                    <span class="size-1.5 rounded-full bg-income"></span>
                    متصل الآن
                </p>
            </div>
        </div>

        <!-- conversation -->
        <div class="space-y-3 p-4">
            <!-- user bubble -->
            <div
                class="flex justify-end animate-fade-in-up"
                style="animation-delay: 80ms;"
            >
                <div
                    class="max-w-[80%] rounded-2xl rounded-te-sm bg-primary px-3.5 py-2.5 text-sm text-primary-foreground"
                >
                    كم صرفت على المطاعم هذا الشهر؟
                </div>
            </div>

            <!-- assistant bubble -->
            <div
                class="flex justify-start animate-fade-in-up"
                style="animation-delay: 260ms;"
            >
                <div
                    class="max-w-[85%] space-y-2.5 rounded-2xl rounded-ts-sm border border-border bg-muted/50 px-3.5 py-3 text-sm text-foreground"
                >
                    <p>
                        صرفت <span
                            class="font-bold tabular-nums text-foreground"
                            >١٬٨٤٠ ر.س</span
                        >
                        على المطاعم هذا الشهر — بزيادة
                        <span class="font-semibold text-expense">١٢٪</span>
                        عن الشهر الماضي.
                    </p>
                    <div
                        class="flex items-center gap-2.5 rounded-xl border border-border bg-card p-2.5"
                    >
                        <CategoryIcon
                            icon="utensils"
                            color="var(--chart-1)"
                            size="sm"
                        />
                        <div class="flex-1">
                            <div class="flex items-baseline justify-between">
                                <span class="text-xs font-medium"
                                    >مطاعم وكافيهات</span
                                >
                                <span
                                    class="text-xs font-semibold tabular-nums text-muted-foreground"
                                    >٢٤ معاملة</span
                                >
                            </div>
                            <div
                                class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-muted"
                            >
                                <div
                                    class="h-full rounded-full"
                                    style="width: 72%; background: var(--chart-1);"
                                ></div>
                            </div>
                        </div>
                    </div>
                    <p class="text-xs text-muted-foreground">
                        نصيحة: أكبر بند كان "طلبات التوصيل" بـ ٦٢٠ ر.س.
                    </p>
                </div>
            </div>

            <!-- quick suggestion -->
            <div
                class="flex justify-start gap-2 animate-fade-in-up"
                style="animation-delay: 440ms;"
            >
                <span
                    class="rounded-full border border-border bg-background px-3 py-1.5 text-xs text-muted-foreground"
                >
                    أضِف مصروف ٥٠ ر.س بنزين
                </span>
            </div>
        </div>

        <!-- input -->
        <div class="border-t border-border p-3">
            <div
                class="flex items-center gap-2 rounded-full border border-border bg-background px-3.5 py-2"
            >
                <span class="flex-1 text-sm text-muted-foreground"
                    >اسأل عن أموالك…</span
                >
                <span
                    class="flex size-8 items-center justify-center rounded-full bg-primary text-primary-foreground"
                >
                    <Send class="size-4 cn-rtl-flip" />
                </span>
            </div>
        </div>
    </div>
</div>
