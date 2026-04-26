<script setup lang="ts">
import type { Chat } from '@/stores/chats'

defineProps<{ chat: Chat }>()
</script>

<template>
  <div class="chat-item soft-card">
    <div class="chat-name">{{ chat.telegram_user?.first_name ?? `Chat #${chat.id}` }}</div>
    <div v-if="chat.last_message" class="chat-preview">{{ chat.last_message.body }}</div>
  </div>
</template>

<style scoped>
.chat-item {
  cursor: pointer;
  transition: transform 0.15s;
}
.chat-item:hover {
  transform: translateY(-2px);
}
.chat-name {
  font-weight: 600;
  color: #1e293b;
  font-size: 1rem;
}
.chat-preview {
  font-size: 0.875rem;
  color: #64748b;
  margin-top: 0.25rem;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>
