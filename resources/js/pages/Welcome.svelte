<script lang="ts">
    import { Link, page } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { toUrl } from '@/lib/utils';
    import { dashboard, login, register } from '@/routes';
    import AppMockup from '@/components/landing/AppMockup.svelte';
    import AssistantChat from '@/components/landing/AssistantChat.svelte';

    import Sparkles from 'lucide-svelte/icons/sparkles';
    import Wallet from 'lucide-svelte/icons/wallet';
    import Bot from 'lucide-svelte/icons/bot';
    import Cloud from 'lucide-svelte/icons/cloud';
    import ChartPie from 'lucide-svelte/icons/chart-pie';
    import WandSparkles from 'lucide-svelte/icons/wand-sparkles';
    import Banknote from 'lucide-svelte/icons/banknote';
    import ArrowLeft from 'lucide-svelte/icons/arrow-left';
    import ScanLine from 'lucide-svelte/icons/scan-line';
    import ShieldCheck from 'lucide-svelte/icons/shield-check';
    import Zap from 'lucide-svelte/icons/zap';

    const auth = $derived(page.props.auth);

    const primaryHref = $derived(
        auth.user ? toUrl(dashboard()) : toUrl(register()),
    );
    const primaryLabel = $derived(
        auth.user ? 'لوحة التحكم' : 'إبدأ مجاناً',
    );

    const features = [
        {
            icon: Wallet,
            title: 'تتبّع المصروفات والدخل',
            desc: 'سجّل كل معاملة في ثوانٍ، وشاهد رصيدك يتحدّث لحظياً.',
        },
        {
            icon: WandSparkles,
            title: 'تصنيف تلقائي بالذكاء الاصطناعي',
            desc: 'يتعرّف ميزان على معاملاتك ويضعها في الفئة الصحيحة تلقائياً.',
        },
        {
            icon: Bot,
            title: 'مساعد مالي محادثي',
            desc: 'اسأل عن أموالك، أضِف معاملة، أو اطلب تحليلاً — بلغتك.',
        },
        {
            icon: ChartPie,
            title: 'تقارير ورسوم بيانية',
            desc: 'رسوم واضحة تكشف أين تذهب أموالك على مدار الشهر.',
        },
        {
            icon: Cloud,
            title: 'مزامنة سحابية',
            desc: 'بياناتك محفوظة ومتزامنة عبر كل أجهزتك في أي وقت.',
        },
        {
            icon: Banknote,
            title: 'بالريال السعودي',
            desc: 'مصمّم للسوق السعودي، بعملة الريال ولغة عربية أصيلة.',
        },
    ];

    const steps = [
        {
            n: '١',
            title: 'سجّل معاملاتك',
            desc: 'أضِف مصروفاتك ودخلك يدوياً أو عبر المساعد المالي بجملة واحدة.',
        },
        {
            n: '٢',
            title: 'يصنّفها ميزان تلقائياً',
            desc: 'الذكاء الاصطناعي يرتّب كل معاملة في فئتها دون أي جهد منك.',
        },
        {
            n: '٣',
            title: 'افهم فلوسك',
            desc: 'تقارير ورؤى فورية تريك أين توفّر وأين تصرف أكثر من اللازم.',
        },
    ];

    const trust = [
        { icon: ScanLine, label: 'تصنيف تلقائي' },
        { icon: Sparkles, label: 'مساعد ذكي' },
        { icon: Zap, label: 'تقارير فورية' },
        { icon: ShieldCheck, label: 'بياناتك محمية' },
    ];

    const navLinks = [
        { href: '#features', label: 'المميزات' },
        { href: '#how', label: 'كيف يعمل' },
        { href: '#assistant', label: 'المساعد' },
    ];
</script>

<AppHead title="ميزان — تتبّع مصاريفك بذكاء" />

{#snippet mark(cls = 'size-5')}
    <svg viewBox="0 0 32 32" fill="none" class={cls}>
        <rect x="6" y="17" width="4.5" height="9" rx="2.25" fill="currentColor" opacity="0.55" />
        <rect x="13.75" y="11" width="4.5" height="15" rx="2.25" fill="currentColor" opacity="0.8" />
        <rect x="21.5" y="6" width="4.5" height="20" rx="2.25" fill="currentColor" />
    </svg>
{/snippet}

<div class="min-h-screen bg-background text-foreground">
    <!-- ===== NAV ===== -->
    <header class="sticky top-0 z-50 border-b border-border/70 bg-background/75 backdrop-blur-md">
        <nav class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-4 sm:px-6">
            <a href="#top" class="flex items-center gap-2.5">
                <span class="flex size-9 items-center justify-center rounded-xl bg-primary text-primary-foreground shadow-soft">
                    {@render mark('size-6')}
                </span>
                <span class="text-lg font-bold tracking-tight">ميزان</span>
            </a>

            <div class="hidden items-center gap-1 md:flex">
                {#each navLinks as l}
                    <a
                        href={l.href}
                        class="rounded-lg px-3 py-2 text-sm font-medium text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                    >
                        {l.label}
                    </a>
                {/each}
            </div>

            <div class="flex items-center gap-2">
                {#if auth.user}
                    <Link
                        href={toUrl(dashboard())}
                        class="inline-flex h-9 items-center rounded-lg bg-primary px-4 text-sm font-medium text-primary-foreground transition-opacity hover:opacity-90"
                    >
                        لوحة التحكم
                    </Link>
                {:else}
                    <Link
                        href={toUrl(login())}
                        class="hidden h-9 items-center rounded-lg px-4 text-sm font-medium text-muted-foreground transition-colors hover:bg-muted hover:text-foreground sm:inline-flex"
                    >
                        تسجيل الدخول
                    </Link>
                    <Link
                        href={toUrl(register())}
                        class="inline-flex h-9 items-center rounded-lg bg-primary px-4 text-sm font-medium text-primary-foreground shadow-soft transition-opacity hover:opacity-90"
                    >
                        إنشاء حساب
                    </Link>
                {/if}
            </div>
        </nav>
    </header>

    <main id="top">
        <!-- ===== HERO ===== -->
        <section class="relative overflow-hidden">
            <!-- soft glows -->
            <div class="pointer-events-none absolute inset-0 -z-10 overflow-hidden">
                <div class="absolute -top-32 start-1/2 size-[42rem] -translate-x-1/2 rounded-full bg-primary/15 blur-3xl dark:bg-primary/20"></div>
                <div class="absolute -bottom-40 -end-20 size-96 rounded-full bg-[var(--chart-4)]/10 blur-3xl"></div>
            </div>

            <div class="mx-auto grid max-w-6xl items-center gap-12 px-4 py-16 sm:px-6 lg:grid-cols-2 lg:gap-8 lg:py-24">
                <!-- copy -->
                <div class="text-center lg:text-start">
                    <span
                        class="inline-flex items-center gap-2 rounded-full border border-border bg-card/70 px-3 py-1 text-xs font-medium text-muted-foreground shadow-soft backdrop-blur animate-fade-in-up"
                    >
                        <Sparkles class="size-3.5 text-primary" />
                        إدارة مصاريف مدعومة بالذكاء الاصطناعي
                    </span>

                    <h1
                        class="mt-5 text-balance text-4xl font-bold leading-[1.15] tracking-tight sm:text-5xl lg:text-6xl animate-fade-in-up"
                        style="animation-delay: 80ms;"
                    >
                        تحكّم في مصاريفك.
                        <span class="text-primary">بذكاء.</span>
                    </h1>

                    <p
                        class="mx-auto mt-5 max-w-lg text-pretty text-base leading-relaxed text-muted-foreground sm:text-lg lg:mx-0 animate-fade-in-up"
                        style="animation-delay: 160ms;"
                    >
                        ميزان يتتبّع مصروفاتك ودخلك، يصنّفها تلقائياً، ويجيب عن أسئلتك المالية —
                        كل ذلك بالريال السعودي وبلغة عربية أصيلة.
                    </p>

                    <div
                        class="mt-8 flex flex-col items-stretch justify-center gap-3 sm:flex-row lg:justify-start animate-fade-in-up"
                        style="animation-delay: 240ms;"
                    >
                        <Link
                            href={primaryHref}
                            class="group inline-flex h-12 items-center justify-center gap-2 rounded-xl bg-primary px-6 text-base font-semibold text-primary-foreground shadow-elevated transition-transform hover:-translate-y-0.5"
                        >
                            {primaryLabel}
                            <ArrowLeft class="size-4.5 transition-transform group-hover:-translate-x-1" />
                        </Link>
                        <a
                            href="#how"
                            class="inline-flex h-12 items-center justify-center gap-2 rounded-xl border border-border bg-card px-6 text-base font-semibold text-foreground shadow-soft transition-colors hover:bg-muted"
                        >
                            شاهد كيف يعمل
                        </a>
                    </div>

                    <!-- trust strip -->
                    <div
                        class="mt-10 flex flex-wrap items-center justify-center gap-x-6 gap-y-3 lg:justify-start animate-fade-in-up"
                        style="animation-delay: 320ms;"
                    >
                        {#each trust as t}
                            <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                <t.icon class="size-4 text-primary" />
                                {t.label}
                            </div>
                        {/each}
                    </div>
                </div>

                <!-- visual -->
                <div class="relative mx-auto w-full max-w-md animate-fade-in-up lg:mx-0" style="animation-delay: 200ms;">
                    <AppMockup />
                </div>
            </div>
        </section>

        <!-- ===== FEATURES ===== -->
        <section id="features" class="border-y border-border bg-muted/30">
            <div class="mx-auto max-w-6xl px-4 py-20 sm:px-6">
                <div class="mx-auto max-w-2xl text-center">
                    <span class="text-sm font-semibold text-primary">المميزات</span>
                    <h2 class="mt-2 text-balance text-3xl font-bold tracking-tight sm:text-4xl">
                        كل ما تحتاجه لإدارة أموالك
                    </h2>
                    <p class="mt-4 text-pretty text-muted-foreground">
                        أدوات ذكية ومترابطة تجعل تتبّع المال بسيطاً وممتعاً — دون جداول ولا تعقيد.
                    </p>
                </div>

                <div class="mt-14 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {#each features as f, i}
                        <div
                            class="group rounded-2xl border border-border bg-card p-6 shadow-soft transition-all hover:-translate-y-1 hover:shadow-elevated animate-fade-in-up"
                            style="animation-delay: {i * 70}ms;"
                        >
                            <span class="flex size-11 items-center justify-center rounded-xl bg-primary/10 text-primary transition-colors group-hover:bg-primary group-hover:text-primary-foreground">
                                <f.icon class="size-5.5" />
                            </span>
                            <h3 class="mt-4 text-lg font-semibold">{f.title}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-muted-foreground">{f.desc}</p>
                        </div>
                    {/each}
                </div>
            </div>
        </section>

        <!-- ===== HOW IT WORKS ===== -->
        <section id="how" class="mx-auto max-w-6xl px-4 py-20 sm:px-6">
            <div class="mx-auto max-w-2xl text-center">
                <span class="text-sm font-semibold text-primary">كيف يعمل</span>
                <h2 class="mt-2 text-balance text-3xl font-bold tracking-tight sm:text-4xl">
                    ثلاث خطوات وأنت في السيطرة
                </h2>
            </div>

            <div class="relative mt-14 grid gap-6 md:grid-cols-3">
                {#each steps as s, i}
                    <div
                        class="relative rounded-2xl border border-border bg-card p-7 shadow-soft animate-fade-in-up"
                        style="animation-delay: {i * 90}ms;"
                    >
                        <span class="flex size-12 items-center justify-center rounded-full bg-primary text-lg font-bold text-primary-foreground shadow-soft tabular-nums">
                            {s.n}
                        </span>
                        <h3 class="mt-5 text-lg font-semibold">{s.title}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-muted-foreground">{s.desc}</p>
                    </div>
                {/each}
            </div>
        </section>

        <!-- ===== ASSISTANT SHOWCASE ===== -->
        <section id="assistant" class="border-y border-border bg-muted/30">
            <div class="mx-auto grid max-w-6xl items-center gap-12 px-4 py-20 sm:px-6 lg:grid-cols-2">
                <div class="text-center lg:text-start">
                    <span class="inline-flex items-center gap-2 rounded-full border border-border bg-card px-3 py-1 text-xs font-medium text-primary shadow-soft">
                        <Bot class="size-3.5" />
                        المساعد المالي
                    </span>
                    <h2 class="mt-4 text-balance text-3xl font-bold tracking-tight sm:text-4xl">
                        اسأل، وسيجيبك ميزان
                    </h2>
                    <p class="mx-auto mt-4 max-w-lg text-pretty leading-relaxed text-muted-foreground lg:mx-0">
                        مساعد ذكي يفهم أسئلتك بالعربية: كم صرفت، أين توفّر، أضِف معاملة، أو اكشف
                        النفقات غير المعتادة — كأنك تتحدث مع مستشار مالي شخصي.
                    </p>

                    <ul class="mx-auto mt-6 max-w-md space-y-3 text-start lg:mx-0">
                        {#each ['يجيب عن أسئلتك المالية فوراً', 'يضيف المعاملات نيابةً عنك', 'يكشف النفقات غير المعتادة والرؤى'] as item}
                            <li class="flex items-center gap-3 text-sm">
                                <span class="flex size-5 shrink-0 items-center justify-center rounded-full bg-income/15 text-income">
                                    <Sparkles class="size-3" />
                                </span>
                                {item}
                            </li>
                        {/each}
                    </ul>
                </div>

                <div class="animate-fade-in-up" style="animation-delay: 120ms;">
                    <AssistantChat />
                </div>
            </div>
        </section>

        <!-- ===== FINAL CTA ===== -->
        <section class="mx-auto max-w-6xl px-4 py-20 sm:px-6">
            <div class="relative overflow-hidden rounded-3xl border border-border bg-primary px-6 py-16 text-center shadow-elevated sm:px-12">
                <div class="pointer-events-none absolute inset-0 opacity-25">
                    <div class="absolute -top-16 start-1/4 size-72 rounded-full bg-primary-foreground/30 blur-3xl"></div>
                    <div class="absolute -bottom-20 end-1/4 size-72 rounded-full bg-primary-foreground/20 blur-3xl"></div>
                </div>
                <div class="relative">
                    <h2 class="mx-auto max-w-2xl text-balance text-3xl font-bold tracking-tight text-primary-foreground sm:text-4xl">
                        ابدأ رحلتك نحو مالٍ أكثر وضوحاً
                    </h2>
                    <p class="mx-auto mt-4 max-w-lg text-pretty text-primary-foreground/85">
                        انضم إلى ميزان اليوم — مجاناً. لا حاجة لبطاقة ائتمان.
                    </p>
                    <div class="mt-8 flex justify-center">
                        <Link
                            href={primaryHref}
                            class="group inline-flex h-12 items-center justify-center gap-2 rounded-xl bg-card px-7 text-base font-semibold text-foreground shadow-elevated transition-transform hover:-translate-y-0.5"
                        >
                            {auth.user ? 'لوحة التحكم' : 'إنشاء حساب مجاني'}
                            <ArrowLeft class="size-4.5 transition-transform group-hover:-translate-x-1" />
                        </Link>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- ===== FOOTER ===== -->
    <footer class="border-t border-border bg-muted/20">
        <div class="mx-auto max-w-6xl px-4 py-14 sm:px-6">
            <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
                <div class="lg:col-span-1">
                    <div class="flex items-center gap-2.5">
                        <span class="flex size-9 items-center justify-center rounded-xl bg-primary text-primary-foreground shadow-soft">
                            {@render mark('size-6')}
                        </span>
                        <span class="text-lg font-bold tracking-tight">ميزان</span>
                    </div>
                    <p class="mt-4 max-w-xs text-sm leading-relaxed text-muted-foreground">
                        تتبّع مصاريفك بذكاء — تطبيق عربي لإدارة الميزانية الشخصية بالريال السعودي.
                    </p>
                </div>

                <div>
                    <h4 class="text-sm font-semibold">المنتج</h4>
                    <ul class="mt-4 space-y-2.5 text-sm text-muted-foreground">
                        <li><a href="#features" class="transition-colors hover:text-foreground">المميزات</a></li>
                        <li><a href="#how" class="transition-colors hover:text-foreground">كيف يعمل</a></li>
                        <li><a href="#assistant" class="transition-colors hover:text-foreground">المساعد المالي</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-sm font-semibold">الشركة</h4>
                    <ul class="mt-4 space-y-2.5 text-sm text-muted-foreground">
                        <li><a href="#" class="transition-colors hover:text-foreground">من نحن</a></li>
                        <li><a href="#" class="transition-colors hover:text-foreground">تواصل معنا</a></li>
                        <li><a href="#" class="transition-colors hover:text-foreground">المدوّنة</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-sm font-semibold">قانوني</h4>
                    <ul class="mt-4 space-y-2.5 text-sm text-muted-foreground">
                        <li><a href="#" class="transition-colors hover:text-foreground">سياسة الخصوصية</a></li>
                        <li><a href="#" class="transition-colors hover:text-foreground">الشروط والأحكام</a></li>
                    </ul>
                </div>
            </div>

            <div class="mt-12 flex flex-col items-center justify-between gap-4 border-t border-border pt-6 text-sm text-muted-foreground sm:flex-row">
                <p>© ٢٠٢٦ ميزان. جميع الحقوق محفوظة.</p>
                <p class="flex items-center gap-1.5">
                    صُنع بعناية للسوق السعودي
                </p>
            </div>
        </div>
    </footer>
</div>
