<script setup lang="ts">
import { onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useEcho } from '@laravel/echo-vue'
import { useChatsStore } from '@/stores/chats'
import MessageBubble from '@/components/MessageBubble.vue'
import MessageInput from '@/components/MessageInput.vue'

const route = useRoute()
const router = useRouter()
const chatId = Number(route.params.id)
const chats = useChatsStore()

const messages = computed(() => chats.messagesByChat[chatId] ?? [])
const chatTitle = computed(() => {
  const chat = chats.list.find((c) => c.id === chatId)
  return chat?.telegram_user?.first_name ?? `Chat #${chatId}`
})

onMounted(() => chats.fetchMessages(chatId))

useEcho(
  `chat.${chatId}`,
  ['.message.received', '.message.sent'],
  (e: any) => chats.pushMessage(chatId, e),
  [],
  'private',
)

function send(body: string) {
  chats.sendMessage(chatId, body)
}
</script>

<template>
  <div class="chat-layout">
    <header class="chat-header glass">
      <button class="back-btn" @click="router.push('/chats')">← Back</button>
      <span class="chat-title">{{ chatTitle }}</span>
    </header>
    <main class="chat-messages">
      <MessageBubble v-for="msg in messages" :key="msg.id" :message="msg" />
    </main>
    <footer class="chat-footer">
      <MessageInput @send="send" />
    </footer>
  </div>
</template>

<style scoped>
.chat-layout {
  height: 100vh;
  display: flex;
  flex-direction: column;
  background: linear-gradient(135deg, #e8edf5 0%, #f0f4fb 100%);
}
.chat-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.875rem 1rem;
  position: sticky;
  top: 0;
  z-index: 10;
}
.back-btn {
  background: none;
  border: none;
  color: #2563eb;
  font-weight: 600;
  cursor: pointer;
  font-size: 0.9375rem;
}
.chat-title {
  font-weight: 700;
  color: #1e293b;
}
.chat-messages {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}
.chat-footer {
  flex-shrink: 0;
}
</style>
