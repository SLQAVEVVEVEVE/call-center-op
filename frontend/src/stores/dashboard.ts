import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import { useChatsStore } from '@/stores/chats'
import { useAuthStore } from '@/stores/auth'
import type { Chat, ChatStatus } from '@/stores/chats'

export const STATUS_ORDER: ChatStatus[] = ['new', 'in_progress', 'resolved']

export const STATUS_LABELS: Record<ChatStatus, string> = {
  new: 'Новый',
  in_progress: 'В работе',
  resolved: 'Закрыт',
}

export type DashboardFilter = 'all' | 'mine' | 'unassigned'

export interface DashboardColumn {
  key: ChatStatus
  label: string
  chats: Chat[]
}

/**
 * Composable view over the chats store. Single source of truth = chats.list;
 * the dashboard exposes columns as a computed without duplicating state.
 */
export const useDashboardStore = defineStore('dashboard', () => {
  const chats = useChatsStore()
  const auth = useAuthStore()

  const filter = ref<DashboardFilter>('all')

  const filteredChats = computed<Chat[]>(() => {
    const myId = auth.user?.id
    return chats.list.filter((c) => {
      if (filter.value === 'mine') return c.assigned_to?.id === myId
      if (filter.value === 'unassigned') return !c.assigned_to
      return true
    })
  })

  const columns = computed<DashboardColumn[]>(() =>
    STATUS_ORDER.map((key) => ({
      key,
      label: STATUS_LABELS[key],
      chats: filteredChats.value
        .filter((c) => (c.status ?? 'new') === key)
        .sort(byLastActivityDesc),
    })),
  )

  /** Counts for the segmented filter — always over the unfiltered list. */
  const counts = computed(() => {
    const myId = auth.user?.id
    return {
      all: chats.list.length,
      mine: chats.list.filter((c) => c.assigned_to?.id === myId).length,
      unassigned: chats.list.filter((c) => !c.assigned_to).length,
    }
  })

  function byLastActivityDesc(a: Chat, b: Chat): number {
    const ta = a.last_message_at ? new Date(a.last_message_at).getTime() : 0
    const tb = b.last_message_at ? new Date(b.last_message_at).getTime() : 0
    return tb - ta
  }

  function setFilter(f: DashboardFilter) {
    filter.value = f
  }

  async function claim(chatId: number, userId: number) {
    return chats.patchChat(chatId, {
      assigned_to_user_id: userId,
      status: 'in_progress',
    })
  }

  async function release(chatId: number) {
    return chats.patchChat(chatId, { assigned_to_user_id: null })
  }

  async function setStatus(chatId: number, status: ChatStatus) {
    return chats.patchChat(chatId, { status })
  }

  return {
    filter,
    columns,
    counts,
    setFilter,
    claim,
    release,
    setStatus,
  }
})
