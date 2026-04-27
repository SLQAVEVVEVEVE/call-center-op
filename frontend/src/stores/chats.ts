import { ref } from 'vue'
import { defineStore } from 'pinia'
import api from '@/api/axios'

export interface TelegramUser {
  id: number
  first_name: string
  last_name?: string
  username?: string
}

export interface Attachment {
  id: number
  kind: 'photo'
  url: string
  width?: number | null
  height?: number | null
}

export interface Message {
  id: number
  chat_id: number
  body: string | null
  direction: 'in' | 'out'
  created_at: string
  attachments?: Attachment[]
}

export type ChatStatus = 'new' | 'in_progress' | 'resolved'

export interface AssignedOperator {
  id: number
  name: string
}

export interface Chat {
  id: number
  status: ChatStatus
  unread_count: number
  last_message_at?: string | null
  telegram_user?: TelegramUser
  assigned_to?: AssignedOperator | null
  last_message?: Message
  updated_at?: string
  created_at?: string
}

export const useChatsStore = defineStore('chats', () => {
  const list = ref<Chat[]>([])
  const messagesByChat = ref<Record<number, Message[]>>({})

  async function fetchChats() {
    const { data } = await api.get('/chats')
    list.value = data.data
  }

  async function fetchMessages(chatId: number) {
    const { data } = await api.get(`/chats/${chatId}/messages`)
    messagesByChat.value[chatId] = data.data
  }

  async function sendMessage(chatId: number, body: string, image?: File | Blob) {
    let response
    if (image) {
      const form = new FormData()
      if (body) form.append('body', body)
      form.append('image', image, image instanceof File ? image.name : 'photo.jpg')
      response = await api.post(`/chats/${chatId}/messages`, form, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
    } else {
      response = await api.post(`/chats/${chatId}/messages`, { body })
    }
    pushMessage(chatId, response.data.data)
  }

  async function patchChat(chatId: number, payload: Partial<Pick<Chat, 'status'>> & { assigned_to_user_id?: number | null }) {
    const { data } = await api.patch(`/chats/${chatId}`, payload)
    upsertChat(data.data)
    return data.data as Chat
  }

  function upsertChat(chat: Chat) {
    const index = list.value.findIndex((c) => c.id === chat.id)
    if (index >= 0) {
      list.value[index] = { ...list.value[index], ...chat }
    } else {
      list.value.unshift(chat)
    }
  }

  function pushMessage(chatId: number, msg: Message) {
    if (!messagesByChat.value[chatId]) {
      messagesByChat.value[chatId] = []
    }
    const exists = messagesByChat.value[chatId].some((m) => m.id === msg.id)
    if (!exists) {
      messagesByChat.value[chatId].push(msg)
    }
  }

  return {
    list,
    messagesByChat,
    fetchChats,
    fetchMessages,
    sendMessage,
    patchChat,
    upsertChat,
    pushMessage,
  }
})
