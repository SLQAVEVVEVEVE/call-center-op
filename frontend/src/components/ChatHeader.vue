<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import type { Chat } from '@/stores/chats'

const props = defineProps<{ chat?: Chat | null }>()
const router = useRouter()
const auth = useAuthStore()

const initials = computed(() => {
  const name = props.chat?.telegram_user?.first_name ?? ''
  return name.charAt(0).toUpperCase() || '?'
})

const avatarHue = computed(() => ((props.chat?.id ?? 0) * 47) % 360)

const statusLabel = computed<Record<string, string>>(() => ({
  new: 'Новый',
  in_progress: 'В работе',
  resolved: 'Закрыт',
}))

const status = computed(() => props.chat?.status ?? 'new')

async function logout() {
  await auth.logout()
  router.push('/login')
}
</script>

<template>
  <header class="glass flex items-center gap-3 px-4 py-3 sticky top-0 z-10">
    <button
      type="button"
      class="flex items-center gap-1 text-[var(--color-brand-500)] hover:text-[var(--color-brand-600)] font-semibold text-sm px-2 py-1 -ml-2 rounded-lg hover:bg-[var(--color-brand-500)]/10 transition"
      @click="router.push('/dashboard')"
      aria-label="Back to dashboard"
    >
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="15 18 9 12 15 6" />
      </svg>
      <span class="hidden sm:inline">Панель</span>
    </button>

    <div
      class="w-10 h-10 rounded-full flex items-center justify-center text-white font-semibold shrink-0"
      :style="{ background: `hsl(${avatarHue}, 65%, 55%)` }"
    >
      {{ initials }}
    </div>

    <div class="flex-1 min-w-0">
      <div class="font-semibold text-[var(--color-ink)] truncate">
        {{ chat?.telegram_user?.first_name ?? `Chat #${chat?.id ?? ''}` }}
      </div>
      <div v-if="chat?.assigned_to" class="text-xs text-[var(--color-ink-muted)] truncate">
        {{ chat.assigned_to.name }}
      </div>
    </div>

    <span :class="['status-pill', `status-pill-${status}`, 'hidden sm:inline-flex']">
      {{ statusLabel[status] }}
    </span>

    <button
      type="button"
      class="text-sm font-semibold text-[var(--color-ink-muted)] hover:text-[var(--color-brand-500)] px-2 py-1 rounded-lg hover:bg-slate-100 transition"
      @click="logout"
      aria-label="Logout"
    >
      Выйти
    </button>
  </header>
</template>
