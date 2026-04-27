<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useDashboardStore } from '@/stores/dashboard'
import type { Chat, ChatStatus } from '@/stores/chats'
import StatusDropdown from '@/components/StatusDropdown.vue'

const props = defineProps<{ chat: Chat }>()
const router = useRouter()
const auth = useAuthStore()
const dashboard = useDashboardStore()

const partnerName = computed(
  () => props.chat.telegram_user?.first_name ?? `Chat #${props.chat.id}`,
)

const initials = computed(() => partnerName.value.charAt(0).toUpperCase())
const avatarHue = computed(() => (props.chat.id * 47) % 360)

const snippet = computed(() => {
  const m = props.chat.last_message
  if (!m) return 'Без сообщений'
  if (m.body) return truncate(m.body, 80)
  if (m.attachments && m.attachments.length > 0) return '📷 Изображение'
  return ''
})

type Ownership = 'mine' | 'other' | 'unassigned'
const ownership = computed<Ownership>(() => {
  if (!props.chat.assigned_to) return 'unassigned'
  return props.chat.assigned_to.id === auth.user?.id ? 'mine' : 'other'
})

function truncate(s: string, n: number): string {
  return s.length > n ? s.slice(0, n - 1) + '…' : s
}

async function claim() {
  if (!auth.user) return
  await dashboard.claim(props.chat.id, auth.user.id)
}

async function release() {
  await dashboard.release(props.chat.id)
}

async function takeOver() {
  if (!auth.user || !props.chat.assigned_to) return
  const ok = window.confirm(
    `Чат сейчас у ${props.chat.assigned_to.name}. Перехватить и взять себе?`,
  )
  if (!ok) return
  await dashboard.claim(props.chat.id, auth.user.id)
}

async function onStatusChange(value: ChatStatus) {
  if (value !== props.chat.status) {
    await dashboard.setStatus(props.chat.id, value)
  }
}

function open() {
  router.push(`/chats/${props.chat.id}`)
}
</script>

<template>
  <div
    class="bg-white rounded-2xl p-4 flex flex-col gap-3 cursor-pointer transition hover:shadow-md"
    :class="
      ownership === 'mine'
        ? 'ring-2 ring-[var(--color-brand-500)]/40 ring-offset-1'
        : ownership === 'other'
          ? 'opacity-90'
          : ''
    "
    style="box-shadow: var(--shadow-card)"
    @click="open"
  >
    <div class="flex items-center gap-3">
      <div class="relative shrink-0">
        <div
          class="w-9 h-9 rounded-full flex items-center justify-center text-white font-semibold text-sm"
          :style="{ background: `hsl(${avatarHue}, 65%, 55%)` }"
        >
          {{ initials }}
        </div>
        <span
          v-if="ownership === 'mine'"
          class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-[var(--color-brand-500)] border-2 border-white flex items-center justify-center"
          aria-label="Мой чат"
        >
          <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12" />
          </svg>
        </span>
      </div>
      <div class="flex-1 min-w-0">
        <div class="flex items-center gap-1.5">
          <div class="font-semibold text-[var(--color-ink)] truncate">{{ partnerName }}</div>
          <span
            v-if="ownership === 'mine'"
            class="text-[10px] font-semibold uppercase tracking-wide px-1.5 py-0.5 rounded-md bg-[var(--color-brand-500)]/10 text-[var(--color-brand-600)]"
            >Мой</span
          >
        </div>
        <div class="text-xs text-[var(--color-ink-muted)] truncate">
          <template v-if="ownership === 'mine'">Назначен: вы</template>
          <template v-else-if="ownership === 'other'">У {{ chat.assigned_to!.name }}</template>
          <template v-else>Не назначен</template>
        </div>
      </div>
      <span
        v-if="chat.unread_count && chat.unread_count > 0"
        class="bg-[var(--color-brand-500)] text-white text-xs font-semibold rounded-full px-2 py-0.5"
        >{{ chat.unread_count }}</span
      >
    </div>

    <p class="text-sm text-[var(--color-ink-muted)] line-clamp-2 break-words">
      {{ snippet }}
    </p>

    <div class="flex items-center gap-2 flex-wrap" @click.stop>
      <button
        v-if="ownership === 'unassigned'"
        type="button"
        class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-[var(--color-brand-500)] text-white hover:bg-[var(--color-brand-600)] transition"
        @click="claim"
      >
        Взять в работу
      </button>
      <button
        v-else-if="ownership === 'mine'"
        type="button"
        class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-slate-100 text-[var(--color-ink-muted)] hover:bg-slate-200 transition"
        @click="release"
      >
        Отпустить
      </button>
      <button
        v-else
        type="button"
        class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-slate-200 text-[var(--color-ink-muted)] hover:bg-slate-50 transition"
        @click="takeOver"
        :title="`Чат у ${chat.assigned_to?.name}. Перехватить?`"
      >
        Перехватить
      </button>

      <StatusDropdown
        :model-value="chat.status"
        :disabled="ownership === 'other'"
        @update:model-value="onStatusChange"
      />

      <button
        type="button"
        class="ml-auto text-xs font-semibold text-[var(--color-brand-500)] hover:text-[var(--color-brand-600)]"
        @click="open"
      >
        Открыть →
      </button>
    </div>
  </div>
</template>
