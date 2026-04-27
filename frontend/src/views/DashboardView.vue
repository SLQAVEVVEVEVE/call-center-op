<script setup lang="ts">
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useEcho } from '@laravel/echo-vue'
import { useAuthStore } from '@/stores/auth'
import { useChatsStore } from '@/stores/chats'
import { useDashboardStore } from '@/stores/dashboard'
import type { DashboardFilter } from '@/stores/dashboard'
import ChatCard from '@/components/ChatCard.vue'

const router = useRouter()
const auth = useAuthStore()
const chats = useChatsStore()
const dashboard = useDashboardStore()

onMounted(() => chats.fetchChats())

useEcho(
  'dashboard',
  '.chat.updated',
  (chat: any) => chats.upsertChat(chat),
  [],
  'private',
)

useEcho(
  `operator.${auth.user!.id}`,
  '.message.received',
  () => chats.fetchChats(),
  [],
  'private',
)

const FILTERS: Array<{ key: DashboardFilter; label: string }> = [
  { key: 'all', label: 'Все' },
  { key: 'mine', label: 'Мои' },
  { key: 'unassigned', label: 'Свободные' },
]

function logout() {
  auth.logout().then(() => router.push('/login'))
}
</script>

<template>
  <div class="min-h-dvh bg-[var(--color-surface)]">
    <header class="glass flex items-center justify-between px-6 py-4 sticky top-0 z-10">
      <h1 class="text-xl font-bold text-[var(--color-ink)]">Панель оператора</h1>
      <div class="flex items-center gap-4">
        <span class="text-sm text-[var(--color-ink-muted)]">
          {{ auth.user?.name }}
        </span>
        <button
          type="button"
          class="text-sm font-semibold text-[var(--color-brand-500)] hover:text-[var(--color-brand-600)]"
          @click="logout"
        >
          Выйти
        </button>
      </div>
    </header>

    <main class="max-w-[1400px] mx-auto px-4 py-6">
      <!-- Segmented filter -->
      <div class="mb-5 flex items-center gap-2">
        <div class="inline-flex bg-white rounded-xl p-1 border border-slate-200">
          <button
            v-for="f in FILTERS"
            :key="f.key"
            type="button"
            class="px-3 py-1.5 text-sm font-medium rounded-lg transition flex items-center gap-1.5"
            :class="
              dashboard.filter === f.key
                ? 'bg-[var(--color-brand-500)] text-white shadow-sm'
                : 'text-[var(--color-ink-muted)] hover:text-[var(--color-ink)]'
            "
            @click="dashboard.setFilter(f.key)"
          >
            {{ f.label }}
            <span
              class="text-xs font-semibold rounded-full px-1.5 py-0.5"
              :class="
                dashboard.filter === f.key
                  ? 'bg-white/20 text-white'
                  : 'bg-slate-100 text-[var(--color-ink-muted)]'
              "
            >
              {{ dashboard.counts[f.key] }}
            </span>
          </button>
        </div>
      </div>

      <p
        v-if="chats.list.length === 0"
        class="text-center text-[var(--color-ink-muted)] py-16"
      >
        Чатов пока нет. Дождитесь сообщения из Telegram.
      </p>

      <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <section
          v-for="col in dashboard.columns"
          :key="col.key"
          class="bg-white/50 rounded-2xl p-3 flex flex-col gap-3 min-h-[300px]"
        >
          <header class="flex items-center justify-between px-2 py-1">
            <h2 class="font-semibold text-[var(--color-ink)]">{{ col.label }}</h2>
            <span
              class="text-xs font-semibold rounded-full px-2 py-0.5 bg-slate-200 text-[var(--color-ink-muted)]"
            >
              {{ col.chats.length }}
            </span>
          </header>

          <div class="flex flex-col gap-3">
            <ChatCard v-for="chat in col.chats" :key="chat.id" :chat="chat" />
            <p
              v-if="col.chats.length === 0"
              class="text-xs text-center text-[var(--color-ink-muted)] py-4"
            >
              —
            </p>
          </div>
        </section>
      </div>
    </main>
  </div>
</template>
