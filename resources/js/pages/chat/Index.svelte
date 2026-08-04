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
  import Bot from 'lucide-svelte/icons/bot';
  import MessageSquareText from 'lucide-svelte/icons/message-square-text';
  import Send from 'lucide-svelte/icons/send';
  import User from 'lucide-svelte/icons/user';
  import AppHead from '@/components/AppHead.svelte';
  import Heading from '@/components/Heading.svelte';
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
    default: 'شكراً على رسالتك. المساعد الذكي غير متاح حالياً وسيتم تفعيله قريباً.',
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
      { id: messageId, text: trimmed, sender: 'user', timestamp: new Date() },
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
    return date.toLocaleTimeString('ar-SA', { hour: '2-digit', minute: '2-digit' });
  }
</script>

<AppHead title="المساعد الذكي" />

<div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
  <Heading
    title="المساعد الذكي"
    description="اسأل عن مصروفاتك، أضف معاملات، واحصل على نصائح مالية"
  />

  <div class="flex-1 flex flex-col overflow-hidden">
    <div
      bind:this={chatContainer}
      class="flex-1 overflow-y-auto space-y-4 px-1 pb-4"
    >
      {#each messages as msg (msg.id)}
        <div class="flex {msg.sender === 'user' ? 'justify-end' : 'justify-start'}">
          <div
            class="flex max-w-[80%] gap-2"
          >
            {#if msg.sender === 'ai'}
              <div class="flex size-7 shrink-0 items-center justify-center rounded-full bg-primary/10 mt-0.5">
                <Bot class="size-3.5 text-primary" />
              </div>
            {/if}

            <div
              class="rounded-xl px-4 py-2.5 {msg.sender === 'user'
                ? 'bg-primary text-primary-foreground rounded-es-sm'
                : 'bg-muted rounded-ee-sm'}"
            >
              <p class="text-sm leading-relaxed whitespace-pre-wrap">{msg.text}</p>
              <p
                class="mt-1 text-[0.65rem] opacity-60 {msg.sender === 'user' ? 'text-right' : 'text-left'}"
              >
                {formatTime(msg.timestamp)}
              </p>
            </div>

            {#if msg.sender === 'user'}
              <div class="flex size-7 shrink-0 items-center justify-center rounded-full bg-primary/10 mt-0.5">
                <User class="size-3.5 text-primary" />
              </div>
            {/if}
          </div>
        </div>
      {/each}

      {#if isThinking}
        <div class="flex justify-start">
          <div class="flex max-w-[80%] gap-2">
            <div class="flex size-7 shrink-0 items-center justify-center rounded-full bg-primary/10 mt-0.5">
              <Bot class="size-3.5 text-primary" />
            </div>
            <div class="rounded-xl rounded-ee-sm bg-muted px-4 py-2.5">
              <div class="flex items-center gap-1.5">
                <span class="inline-block size-1.5 animate-bounce rounded-full bg-muted-foreground/60 [animation-delay:0s]"></span>
                <span class="inline-block size-1.5 animate-bounce rounded-full bg-muted-foreground/60 [animation-delay:0.15s]"></span>
                <span class="inline-block size-1.5 animate-bounce rounded-full bg-muted-foreground/60 [animation-delay:0.3s]"></span>
              </div>
            </div>
          </div>
        </div>
      {/if}

      {#if messages.length === 1 && messages[0].id === -1}
        <div class="flex flex-col items-center justify-center gap-4 pt-6">
          <div class="flex size-14 items-center justify-center rounded-full bg-primary/10">
            <MessageSquareText class="size-7 text-primary" />
          </div>
          <p class="text-sm text-muted-foreground text-center">
            ابدأ محادثة مع مساعدك المالي الذكي
          </p>
          <div class="flex flex-wrap justify-center gap-2">
            {#each suggestions as suggestion (suggestion)}
              <button
                class="rounded-full border px-3 py-1.5 text-xs hover:bg-muted transition-colors text-muted-foreground"
                onclick={() => sendMessage(suggestion)}
              >
                {suggestion}
              </button>
            {/each}
          </div>
        </div>
      {/if}
    </div>

    <div class="border-t pt-3">
      <div class="flex flex-wrap gap-2 mb-3">
        {#each suggestions as suggestion (suggestion)}
          <button
            class="rounded-full border px-3 py-1.5 text-xs hover:bg-muted transition-colors text-muted-foreground"
            onclick={() => sendMessage(suggestion)}
          >
            {suggestion}
          </button>
        {/each}
      </div>

      <div class="flex items-center gap-2">
        <Input
          bind:ref={inputRef}
          bind:value={inputValue}
          placeholder="اكتب رسالتك هنا..."
          onkeydown={handleKeydown}
          disabled={isThinking}
          class="flex-1"
        />
        <Button
          onclick={() => sendMessage(inputValue)}
          disabled={isThinking || !inputValue.trim()}
          size="icon"
        >
          <Send class="size-4" />
        </Button>
      </div>
    </div>
  </div>
</div>
