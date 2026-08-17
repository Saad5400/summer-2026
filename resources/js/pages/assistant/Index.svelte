<script module lang="ts">
    import { assistant } from '@/routes';

    export const layout = {
        breadcrumbs: [
            {
                title: 'المساعد المالي',
                href: assistant(),
            },
        ],
    };
</script>

<script lang="ts">
    import ArrowUp from 'lucide-svelte/icons/arrow-up';
    import Bot from 'lucide-svelte/icons/bot';
    import MessageCircleQuestion from 'lucide-svelte/icons/message-circle-question';
    import RotateCcw from 'lucide-svelte/icons/rotate-ccw';
    import Square from 'lucide-svelte/icons/square';
    import { untrack } from 'svelte';
    import AppHead from '@/components/AppHead.svelte';
    import MarkdownContent from '@/components/assistant/MarkdownContent.svelte';
    import ToolCallCard from '@/components/assistant/ToolCallCard.svelte';
    import type { ToolCallView } from '@/components/assistant/ToolCallCard.svelte';
    import Heading from '@/components/Heading.svelte';
    import { Button } from '@/components/ui/button';
    import { Textarea } from '@/components/ui/textarea';
    import { streamAssistant } from '@/lib/assistant-stream';
    import type {
        AssistantFrame,
        AssistantHistoryMessage,
    } from '@/lib/assistant-stream';

    type Message =
        | { id: string; role: 'user'; content: string }
        | {
              id: string;
              role: 'assistant';
              content: string;
              error?: boolean;
              retry?: () => void;
          }
        | { id: string; role: 'tool'; call: ToolCallView };

    let messages = $state<Message[]>([]);
    let input = $state('');
    let streaming = $state(false);

    let scrollContainer: HTMLElement | null = $state(null);
    let textareaEl: HTMLTextAreaElement | null = $state(null);

    let history = $derived(
        messages
            .filter(
                (
                    message,
                ): message is Extract<
                    Message,
                    { role: 'user' | 'assistant' }
                > => message.role !== 'tool',
            )
            .filter((message) => message.content.trim() !== '')
            .slice(-20)
            .map((message) => ({
                role: message.role,
                content: message.content,
            })),
    );

    const EXAMPLES = [
        'كم صرفت هذا الشهر؟',
        'أضف مصروف ٥٠ ريال قهوة أمس',
        'اعرض أكبر ٥ مصروفات',
        'احذف آخر عملية',
    ];

    let showTypingIndicator = $derived.by(() => {
        if (!streaming) {
            return false;
        }

        const last = messages.length > 0 ? messages[messages.length - 1] : null;

        if (last === null) {
            return true;
        }

        if (last.role === 'tool') {
            return false;
        }

        if (last.role === 'assistant' && !last.error && last.content !== '') {
            return false;
        }

        return true;
    });

    let uid = 0;

    function nextId(): string {
        uid += 1;

        return `m${uid}`;
    }

    /**
     * Scroll to the bottom whenever the transcript grows — either a new
     * message arrives or streaming text extends the last assistant bubble.
     */
    function scheduleAutoscroll(
        messageCount: number,
        lastContentLength: number,
    ): void {
        untrack(() => {
            queueMicrotask(() => {
                void messageCount;
                void lastContentLength;

                scrollContainer?.scrollTo({
                    top: scrollContainer.scrollHeight,
                    behavior: 'smooth',
                });
            });
        });
    }

    $effect(() => {
        const last = messages.length > 0 ? messages[messages.length - 1] : null;

        scheduleAutoscroll(
            messages.length,
            last !== null && last.role !== 'tool' ? last.content.length : 0,
        );
    });

    function appendToolCallFrame(
        frame: Extract<AssistantFrame, { type: 'tool_call' }>,
    ): void {
        messages.push({
            id: nextId(),
            role: 'tool',
            call: {
                id: frame.id,
                name: frame.name,
                arguments: frame.arguments,
                status: 'running',
            },
        });
    }

    function applyToolResultFrame(
        frame: Extract<AssistantFrame, { type: 'tool_result' }>,
    ): void {
        for (let index = messages.length - 1; index >= 0; index -= 1) {
            const message = messages[index];

            if (message.role === 'tool' && message.call.id === frame.id) {
                message.call.status = frame.ok ? 'success' : 'failed';
                message.call.summary = frame.summary;
                message.call.data = frame.data;

                return;
            }
        }
    }

    function appendDelta(delta: string): void {
        const last = messages[messages.length - 1];

        if (last && last.role === 'assistant' && !last.error) {
            last.content += delta;

            return;
        }

        messages.push({ id: nextId(), role: 'assistant', content: delta });
    }

    let controller: AbortController | null = null;

    async function send(messageText: string): Promise<void> {
        const trimmed = messageText.trim();

        if (trimmed === '' || streaming) {
            return;
        }

        messages.push({ id: nextId(), role: 'user', content: trimmed });

        input = '';

        await run(trimmed);
    }

    async function run(messageText: string): Promise<void> {
        streaming = true;

        controller = new AbortController();

        const historySnapshot: AssistantHistoryMessage[] = [...history];

        try {
            await streamAssistant(
                messageText,
                historySnapshot,
                (frame) => {
                    switch (frame.type) {
                        case 'text':
                            appendDelta(frame.delta);

                            break;
                        case 'tool_call':
                            appendToolCallFrame(frame);

                            break;
                        case 'tool_result':
                            applyToolResultFrame(frame);

                            break;
                        case 'error':
                            messages.push({
                                id: nextId(),
                                role: 'assistant',
                                content: frame.message,
                                error: true,
                                retry: () => retry(messageText),
                            });

                            break;
                        case 'done':
                            break;
                    }
                },
                controller.signal,
            );
        } catch (error) {
            if ((error as Error).name !== 'AbortError') {
                messages.push({
                    id: nextId(),
                    role: 'assistant',
                    content:
                        'تعذر الاتصال بالمساعد. تحقق من اتصالك وحاول مرة أخرى.',
                    error: true,
                    retry: () => retry(messageText),
                });
            }
        } finally {
            controller = null;
            streaming = false;

            queueMicrotask(() => textareaEl?.focus());
        }
    }

    function retry(messageText: string): void {
        if (streaming) {
            return;
        }

        // Drop trailing error bubbles.
        while (messages.length > 0) {
            const last = messages[messages.length - 1];

            if (last.role === 'assistant' && last.error) {
                messages.pop();

                continue;
            }

            break;
        }

        void run(messageText);
    }

    function stop(): void {
        controller?.abort();
        controller = null;
        streaming = false;
    }

    function resetConversation(): void {
        stop();
        messages = [];
    }

    function handleKeydown(event: KeyboardEvent): void {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();

            void send(input);
        }
    }
</script>

<AppHead title="المساعد المالي" />

<div class="flex h-[calc(100dvh-4rem)] flex-1 flex-col">
    <div
        class="flex flex-wrap items-center justify-between gap-3 border-b px-4 py-4 md:px-6"
    >
        <Heading
            title="المساعد المالي"
            description="اسأل عن مصروفاتك، أضف أو عدّل أو احذف عمليات — كل شيء بالمحادثة"
        />
        <Button
            variant="outline"
            size="sm"
            onclick={resetConversation}
            disabled={messages.length === 0}
        >
            <RotateCcw />
            محادثة جديدة
        </Button>
    </div>

    <div
        bind:this={scrollContainer}
        class="flex-1 overflow-y-auto px-4 py-6 md:px-6"
        aria-live="polite"
        aria-label="منطقة الرسائل"
    >
        <div class="mx-auto flex w-full max-w-3xl flex-col gap-4">
            {#if messages.length === 0}
                <div
                    class="flex flex-1 flex-col items-center justify-center gap-6 py-16 text-center"
                >
                    <span
                        class="flex size-14 items-center justify-center rounded-2xl bg-primary/10"
                    >
                        <Bot class="size-7 text-primary" />
                    </span>
                    <div class="space-y-1.5">
                        <h3 class="text-base font-semibold">
                            كيف أقدر أساعدك؟
                        </h3>
                        <p class="text-sm text-muted-foreground">
                            ابدأ محادثة أو جرّب أحد الأمثلة التالية
                        </p>
                    </div>
                    <div class="grid w-full gap-2 sm:grid-cols-2">
                        {#each EXAMPLES as example (example)}
                            <button
                                type="button"
                                class="flex items-center gap-2 rounded-xl border bg-card px-4 py-3 text-start text-sm transition-colors hover:bg-muted/50"
                                onclick={() => void send(example)}
                            >
                                <MessageCircleQuestion
                                    class="size-4 shrink-0 text-muted-foreground"
                                />
                                <span>{example}</span>
                            </button>
                        {/each}
                    </div>
                </div>
            {:else}
                {#each messages as message (message.id)}
                    {#if message.role === 'user'}
                        <div class="flex justify-end">
                            <div
                                class="max-w-[85%] whitespace-pre-wrap rounded-2xl rounded-ee-md bg-primary px-4 py-2.5 text-sm text-primary-foreground"
                                dir="auto"
                            >
                                {message.content}
                            </div>
                        </div>
                    {:else if message.role === 'tool'}
                        <div class="flex justify-center">
                            <div class="w-full max-w-xl">
                                <ToolCallCard call={message.call} />
                            </div>
                        </div>
                    {:else}
                        <div class="flex items-start gap-2.5">
                            <span
                                class="mt-1 flex size-7 shrink-0 items-center justify-center rounded-lg bg-primary/10"
                                aria-hidden="true"
                            >
                                <Bot class="size-4 text-primary" />
                            </span>
                            <div class="flex max-w-[85%] flex-col gap-1.5">
                                <div
                                    class="rounded-2xl rounded-es-md border px-4 py-2.5 text-sm
                                        {message.error
                                        ? 'whitespace-pre-wrap border-destructive/40 bg-destructive/10 text-destructive'
                                        : 'bg-card'}"
                                    dir="auto"
                                >
                                    {#if message.error}
                                        {message.content}
                                    {:else}
                                        <MarkdownContent
                                            source={message.content}
                                        />
                                    {/if}
                                </div>
                                {#if message.retry}
                                    <button
                                        type="button"
                                        class="flex w-fit items-center gap-1.5 rounded-md text-xs text-muted-foreground transition-colors hover:text-foreground"
                                        onclick={message.retry}
                                        disabled={streaming}
                                    >
                                        <RotateCcw class="size-3" />
                                        إعادة المحاولة
                                    </button>
                                {/if}
                            </div>
                        </div>
                    {/if}
                {/each}

                {#if showTypingIndicator}
                    <div class="flex items-center gap-2.5">
                        <span
                            class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-primary/10"
                            aria-hidden="true"
                        >
                            <Bot class="size-4 text-primary" />
                        </span>
                        <div
                            class="flex items-center gap-1.5 rounded-2xl rounded-es-md border bg-card px-4 py-3"
                        >
                            <span
                                class="size-1.5 animate-pulse rounded-full bg-muted-foreground/60"
                            ></span>
                            <span
                                class="size-1.5 animate-pulse rounded-full bg-muted-foreground/60 [animation-delay:150ms]"
                            ></span>
                            <span
                                class="size-1.5 animate-pulse rounded-full bg-muted-foreground/60 [animation-delay:300ms]"
                            ></span>
                        </div>
                    </div>
                {/if}
            {/if}
        </div>
    </div>

    <div class="border-t bg-background px-4 py-4 md:px-6">
        <div class="mx-auto w-full max-w-3xl">
            <div class="flex items-end gap-2">
                <Textarea
                    ref={textareaEl}
                    bind:value={input}
                    onkeydown={handleKeydown}
                    rows={1}
                    placeholder="اكتب رسالتك… (Enter للإرسال، Shift+Enter لسطر جديد)"
                    disabled={streaming}
                    class="max-h-40 min-h-11 resize-none"
                    dir="auto"
                />
                {#if streaming}
                    <Button
                        type="button"
                        variant="destructive"
                        size="icon"
                        onclick={stop}
                        aria-label="إيقاف التوليد"
                    >
                        <Square class="size-4" />
                    </Button>
                {:else}
                    <Button
                        type="button"
                        size="icon"
                        onclick={() => void send(input)}
                        disabled={input.trim() === ''}
                        aria-label="إرسال"
                    >
                        <ArrowUp class="size-4" />
                    </Button>
                {/if}
            </div>
            <p class="mt-2 text-center text-xs text-muted-foreground">
                المساعد ينفّذ التعديلات مباشرة على بياناتك — راجع الملخص بعد كل
                عملية
            </p>
        </div>
    </div>
</div>
