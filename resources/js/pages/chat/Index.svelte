<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'المساعد الذكي',
                href: '/chat',
            },
        ],
    };
</script>

<script lang="ts">
    import Send from 'lucide-svelte/icons/send';
    import Sparkles from 'lucide-svelte/icons/sparkles';
    import AppHead from '@/components/AppHead.svelte';
    import AssistantAvatar from '@/components/assistant/AssistantAvatar.svelte';
    import TypingIndicator from '@/components/assistant/TypingIndicator.svelte';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';

    interface Message {
        id: number;
        text: string;
        sender: 'user' | 'ai';
        timestamp: Date;
    }

    const dummyResponses: Record<string, string> = {
        كم: 'هذه الميزة غير متاحة حالياً. سيتم تفعيل المساعد الذكي قريباً.',
        أضف: 'هذه الميزة غير متاحة حالياً. سيتم تفعيل المساعد الذكي قريباً.',
        كيف: 'هذه الميزة غير متاحة حالياً. سيتم تفعيل المساعد الذكي قريباً.',
        أظهر: 'هذه الميزة غير متاحة حالياً. سيتم تفعيل المساعد الذكي قريباً.',
        default:
            'شكراً على رسالتك. المساعد الذكي غير متاح حالياً وسيتم تفعيله قريباً.',
    };

    const greetingMessage: Message = {
        id: -1,
        text: 'أهلاً بك! أنا مساعدك المالي. قريباً سأتمكن من مساعدتك في تتبع مصروفاتك وإضافة معاملات جديدة. هذه نسخة تجريبية للواجهة فقط.',
        sender: 'ai',
        timestamp: new Date(),
    };

    let messages: Message[] = $state([greetingMessage]);
    let inputValue: string = $state('');
    let isThinking: boolean = $state(false);
    let messageId: number = $state(0);
    let chatContainer: HTMLDivElement | null = $state(null);
    let inputRef: HTMLInputElement | null = $state(null);

    const suggestions: string[] = [
        'كم صرفت على الأكل الشهر هذا؟',
        'أضف ٤٥ ريال مشتريات بقالة',
        'كيف وضع صرفي هذا الشهر؟',
        'أظهر لي تقرير المصروفات',
    ];

    $effect(() => {
        void messages.length;
        setTimeout(() => {
            if (chatContainer) {
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }
        }, 50);
    });

    function getDummyResponse(text: string): string {
        for (const [key, response] of Object.entries(dummyResponses)) {
            if (text.includes(key)) {
                return response;
            }
        }
        return dummyResponses.default;
    }

    async function sendMessage(text: string) {
        const trimmed = text.trim();

        if (!trimmed || isThinking) {
            return;
        }

        messageId += 1;
        messages = [
            ...messages,
            {
                id: messageId,
                text: trimmed,
                sender: 'user',
                timestamp: new Date(),
            },
        ];

        inputValue = '';
        isThinking = true;

        setTimeout(() => {
            messageId += 1;
            messages = [
                ...messages,
                {
                    id: messageId,
                    text: getDummyResponse(trimmed),
                    sender: 'ai',
                    timestamp: new Date(),
                },
            ];
            isThinking = false;
            inputRef?.focus();
        }, 1000);
    }

    function handleKeydown(event: KeyboardEvent) {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            sendMessage(inputValue);
        }
    }

    function formatTime(date: Date): string {
        return date.toLocaleTimeString('ar', {
            hour: '2-digit',
            minute: '2-digit',
        });
    }
</script>

<AppHead title="المساعد الذكي" />

<div class="-mb-24 flex h-[calc(100dvh-4rem)] flex-1 flex-col md:-mb-8">
    <!-- Header -->
    <header
        class="flex items-center gap-3 border-b border-border/60 bg-background/80 px-4 py-3 backdrop-blur-xl md:px-6"
    >
        <AssistantAvatar size="md" />
        <div class="min-w-0">
            <h1 class="truncate text-sm font-semibold">المساعد الذكي</h1>
            <p class="flex items-center gap-1.5 text-xs text-muted-foreground">
                <span
                    class="size-1.5 rounded-full bg-income {isThinking
                        ? 'animate-pulse'
                        : ''}"
                ></span>
                {isThinking ? 'يكتب الآن…' : 'نسخة تجريبية'}
            </p>
        </div>
    </header>

    <!-- Messages -->
    <div
        bind:this={chatContainer}
        class="flex-1 overflow-y-auto scroll-smooth px-4 py-6 md:px-6"
        aria-live="polite"
        aria-label="منطقة الرسائل"
    >
        <div class="mx-auto flex w-full max-w-3xl flex-col gap-5">
            {#each messages as msg (msg.id)}
                {#if msg.sender === 'user'}
                    <div class="flex animate-fade-in-up justify-end">
                        <div class="flex max-w-[85%] flex-col items-end gap-1">
                            <div
                                class="whitespace-pre-wrap rounded-2xl rounded-ee-md bg-primary px-4 py-2.5 text-sm leading-relaxed text-primary-foreground shadow-soft"
                                dir="auto"
                            >
                                {msg.text}
                            </div>
                            <span
                                class="px-1 text-[0.65rem] text-muted-foreground"
                                >{formatTime(msg.timestamp)}</span
                            >
                        </div>
                    </div>
                {:else}
                    <div class="flex animate-fade-in-up items-start gap-2.5">
                        <AssistantAvatar size="sm" class="mt-0.5" />
                        <div class="flex min-w-0 max-w-[85%] flex-col gap-1">
                            <div
                                class="rounded-2xl rounded-ss-md border border-border/70 bg-card px-4 py-3 text-sm leading-relaxed shadow-soft"
                                dir="auto"
                            >
                                <p class="whitespace-pre-wrap">{msg.text}</p>
                            </div>
                            <span
                                class="px-1 text-[0.65rem] text-muted-foreground"
                                >{formatTime(msg.timestamp)}</span
                            >
                        </div>
                    </div>
                {/if}
            {/each}

            {#if isThinking}
                <div class="flex animate-fade-in items-start gap-2.5">
                    <AssistantAvatar size="sm" class="mt-0.5" />
                    <div
                        class="flex items-center rounded-2xl rounded-ss-md border border-border/70 bg-card px-4 py-3.5 shadow-soft"
                    >
                        <TypingIndicator />
                    </div>
                </div>
            {/if}

            {#if messages.length === 1 && messages[0].id === -1}
                <div class="flex flex-col items-center gap-3 pt-2">
                    <p class="text-xs text-muted-foreground">
                        جرّب أحد الأمثلة
                    </p>
                    <div class="flex flex-wrap justify-center gap-2">
                        {#each suggestions as suggestion (suggestion)}
                            <button
                                class="rounded-full border border-border/70 bg-card px-3.5 py-2 text-xs shadow-soft transition-all hover:border-primary/40 hover:bg-accent/50 active:scale-95"
                                onclick={() => sendMessage(suggestion)}
                            >
                                {suggestion}
                            </button>
                        {/each}
                    </div>
                </div>
            {/if}
        </div>
    </div>

    <!-- Composer -->
    <div
        class="border-t border-border/60 bg-background/80 px-4 pb-[calc(env(safe-area-inset-bottom)+5.5rem)] pt-3 backdrop-blur-xl md:pb-4 md:pt-4"
    >
        <div class="mx-auto w-full max-w-3xl">
            <div
                class="flex items-center gap-2 rounded-2xl border border-border/70 bg-card p-1.5 shadow-soft transition-colors focus-within:border-primary/50 focus-within:ring-2 focus-within:ring-primary/15"
            >
                <Input
                    bind:ref={inputRef}
                    bind:value={inputValue}
                    placeholder="اكتب رسالتك هنا…"
                    onkeydown={handleKeydown}
                    disabled={isThinking}
                    class="h-11 flex-1 border-0 bg-transparent px-3 shadow-none focus-visible:ring-0 dark:bg-transparent"
                    dir="auto"
                />
                <Button
                    onclick={() => sendMessage(inputValue)}
                    disabled={isThinking || !inputValue.trim()}
                    size="icon"
                    class="size-11 shrink-0 rounded-xl transition-transform active:scale-90"
                    aria-label="إرسال"
                >
                    <Send class="size-4.5 cn-rtl-flip" />
                </Button>
            </div>
            <p
                class="mt-2 flex items-center justify-center gap-1.5 text-center text-[0.7rem] text-muted-foreground"
            >
                <Sparkles class="size-3" />
                نسخة تجريبية للواجهة — سيتم تفعيل المساعد قريباً
            </p>
        </div>
    </div>
</div>
