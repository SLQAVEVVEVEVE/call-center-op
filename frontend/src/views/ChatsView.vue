<script setup lang="ts">
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useEcho } from '@laravel/echo-vue'
import { useAuthStore } from '@/stores/auth'
import { useChatsStore } from '@/stores/chats'
import ChatListItem from '@/components/ChatListItem.vue'

defineProps<{ compact?: boolean }>()

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
  <div class="min-h-dvh flex flex-col bg-gradient-to-br from-slate-100 to-slate-50">
    <header
      class="glass flex items-center justify-between px-6 py-4 sticky top-0 z-10"
    >
      <div class="flex items-center gap-3">
        <button
          type="button"
          class="text-sm font-semibold text-[var(--color-brand-500)]"
          @click="router.push('/dashboard')"
        >
          ← Панель
        </button>
        <span class="text-xl font-bold text-[var(--color-ink)]">Чаты</span>
      </div>
      <button
        type="button"
        class="text-sm font-semibold text-[var(--color-brand-500)] hover:text-[var(--color-brand-600)]"
        @click="logout"
      >
        Выйти
      </button>
    </header>
    <main class="flex-1 px-4 py-4 w-full max-w-[600px] mx-auto flex flex-col gap-3">
      <p
        v-if="chats.list.length === 0"
        class="text-center text-[var(--color-ink-muted)] mt-16"
      >
        Чатов пока нет. Дождитесь сообщения из Telegram.
      </p>
      <ChatListItem
        v-for="chat in chats.list"
        :key="chat.id"
        :chat="chat"
        :compact="compact"
        @click="router.push(`/chats/${chat.id}`)"
      />
    </main>
  </div>
</template>
