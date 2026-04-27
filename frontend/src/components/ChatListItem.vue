<script setup lang="ts">
import { computed } from 'vue'
import type { Chat } from '@/stores/chats'

const props = defineProps<{ chat: Chat; compact?: boolean }>()

const snippet = computed(() => {
  const m = props.chat.last_message
  if (!m) return ''
  if (m.body) return m.body
  if (m.attachments && m.attachments.length > 0) return '📷 Изображение'
  return ''
})

const partnerName = computed(
  () => props.chat.telegram_user?.first_name ?? `Chat #${props.chat.id}`,
)

const initials = computed(() => partnerName.value.charAt(0).toUpperCase())
const avatarHue = computed(() => (props.chat.id * 47) % 360)
</script>

<template>
  <div
    class="bg-white rounded-2xl px-4 py-3 flex items-center gap-3 cursor-pointer hover:shadow-md transition"
    style="box-shadow: var(--shadow-card)"
  >
    <div
      class="w-10 h-10 rounded-full flex items-center justify-center text-white font-semibold shrink-0"
      :style="{ background: `hsl(${avatarHue}, 65%, 55%)` }"
    >
      {{ initials }}
    </div>
    <div class="flex-1 min-w-0">
      <div class="font-semibold text-[var(--color-ink)] truncate">{{ partnerName }}</div>
      <div
        v-if="!compact && snippet"
        class="text-sm text-[var(--color-ink-muted)] truncate"
      >
        {{ snippet }}
      </div>
    </div>
    <span
      v-if="chat.unread_count && chat.unread_count > 0"
      class="bg-[var(--color-brand-500)] text-white text-xs font-semibold rounded-full px-2 py-0.5"
    >
      {{ chat.unread_count }}
    </span>
  </div>
</template>
