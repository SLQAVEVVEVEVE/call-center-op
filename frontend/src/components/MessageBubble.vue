<script setup lang="ts">
import type { Message, Attachment } from '@/stores/chats'

defineProps<{ message: Message }>()
const emit = defineEmits<{ open: [attachment: Attachment] }>()

function open(a: Attachment) {
  emit('open', a)
}
</script>

<template>
  <div
    class="flex my-1"
    :class="message.direction === 'out' ? 'justify-end' : 'justify-start'"
  >
    <div
      class="max-w-[clamp(220px,65%,560px)] px-4 py-2.5 rounded-2xl text-[15px] leading-relaxed shadow-sm"
      :class="
        message.direction === 'out'
          ? 'bg-[var(--color-brand-500)] text-white rounded-br-sm'
          : 'bg-[var(--color-bubble-in)] text-[var(--color-bubble-in-text)] rounded-bl-sm'
      "
    >
      <div
        v-if="message.attachments && message.attachments.length > 0"
        class="flex flex-col gap-2"
        :class="message.body ? 'mb-2' : ''"
      >
        <img
          v-for="a in message.attachments"
          :key="a.id"
          :src="a.url"
          class="max-w-[min(280px,100%)] rounded-xl cursor-zoom-in object-cover"
          loading="lazy"
          alt=""
          @click="open(a)"
        />
      </div>
      <div v-if="message.body" class="whitespace-pre-wrap break-words">{{ message.body }}</div>
    </div>
  </div>
</template>
