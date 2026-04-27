<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useEcho } from '@laravel/echo-vue'
import { useChatsStore } from '@/stores/chats'
import type { Attachment } from '@/stores/chats'
import MessageBubble from '@/components/MessageBubble.vue'
import MessageInput from '@/components/MessageInput.vue'
import ChatHeader from '@/components/ChatHeader.vue'
import ImageLightbox from '@/components/ImageLightbox.vue'

const route = useRoute()
const router = useRouter()
const chatId = Number(route.params.id)
const chats = useChatsStore()

const messages = computed(() => chats.messagesByChat[chatId] ?? [])
const currentChat = computed(() => chats.list.find((c) => c.id === chatId) ?? null)

const lightboxOpen = ref(false)
const lightboxSrc = ref<string | null>(null)

function openLightbox(a: Attachment) {
  lightboxSrc.value = a.url
  lightboxOpen.value = true
}

chats.fetchMessages(chatId)
if (chats.list.length === 0) {
  chats.fetchChats()
}

useEcho(
  `chat.${chatId}`,
  ['.message.received', '.message.sent'],
  (e: any) => chats.pushMessage(chatId, e),
  [],
  'private',
)

async function send(payload: { body: string; image?: Blob }) {
  await chats.sendMessage(chatId, payload.body, payload.image)
}
</script>

<template>
  <div class="flex flex-col h-dvh bg-gradient-to-br from-slate-100 to-slate-50">
    <ChatHeader :chat="currentChat" />

    <main class="flex-1 overflow-y-auto px-4 py-3">
      <div class="mx-auto w-full max-w-[var(--max-w-thread)] flex flex-col gap-1">
        <TransitionGroup name="msg" tag="div" class="flex flex-col gap-1">
          <MessageBubble
            v-for="msg in messages"
            :key="msg.id"
            :message="msg"
            @open="openLightbox"
          />
        </TransitionGroup>
      </div>
    </main>

    <footer class="shrink-0">
      <div class="mx-auto w-full max-w-[var(--max-w-thread)]">
        <MessageInput @send="send" />
      </div>
    </footer>

    <ImageLightbox v-model:open="lightboxOpen" :src="lightboxSrc" />
  </div>
</template>
