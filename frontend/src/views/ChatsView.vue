<script setup lang="ts">
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useEcho } from '@laravel/echo-vue'
import { useAuthStore } from '@/stores/auth'
import { useChatsStore } from '@/stores/chats'
import ChatListItem from '@/components/ChatListItem.vue'

const router = useRouter()
const auth = useAuthStore()
const chats = useChatsStore()

onMounted(() => chats.fetchChats())

useEcho(
  `operator.${auth.user!.id}`,
  '.message.received',
  () => chats.fetchChats(),
  [],
  'private',
)

function logout() {
  auth.logout().then(() => router.push('/login'))
}
</script>

<template>
  <div class="chats-layout">
    <header class="chats-header glass">
      <span class="chats-title">Chats</span>
      <button class="logout-btn" @click="logout">Logout</button>
    </header>
    <main class="chats-list">
      <p v-if="chats.list.length === 0" class="empty-state">
        No chats yet. Wait for a Telegram message.
      </p>
      <ChatListItem
        v-for="chat in chats.list"
        :key="chat.id"
        :chat="chat"
        @click="router.push(`/chats/${chat.id}`)"
      />
    </main>
  </div>
</template>

<style scoped>
.chats-layout {
  min-height: 100vh;
  background: linear-gradient(135deg, #e8edf5 0%, #f0f4fb 100%);
  display: flex;
  flex-direction: column;
}
.chats-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.5rem;
  position: sticky;
  top: 0;
  z-index: 10;
}
.chats-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e293b;
}
.logout-btn {
  background: none;
  border: none;
  color: #2563eb;
  font-weight: 600;
  cursor: pointer;
  font-size: 0.9375rem;
}
.chats-list {
  flex: 1;
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  max-width: 600px;
  width: 100%;
  margin: 0 auto;
}
.empty-state {
  text-align: center;
  color: #94a3b8;
  margin-top: 4rem;
}
</style>
